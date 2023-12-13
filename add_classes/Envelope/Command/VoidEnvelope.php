<?php

declare(strict_types=1);

namespace App\Envelope\Command;

use App\Common\Contracts\Notifier\SystemChannel;
use App\Common\Database\Model;
use App\Common\DigitalSignature\Provider\ProviderResolverAwareTrait;
use App\Common\DigitalSignature\Provider\ProviderResolverInterface as SigningProviderResolverInterface;
use App\Common\Exceptions\NotFoundException;
use App\Envelope\Bridge\Notifier\Notification\OrderDocumentEnvelopeNotification;
use App\Envelope\Bridge\Notifier\Sender;
use App\Envelope\Bridge\Notifier\Type;
use App\Envelope\EnvelopeAccessTrait;
use App\Envelope\EnvelopeStatuses;
use App\Envelope\Event\CompleteRouting;
use App\Envelope\Exception\WriteException;
use App\Envelope\File\StorageInterface as FileStorageInterface;
use App\Envelope\HistoryAwareTrait;
use App\Envelope\HistoryEvent;
use App\Envelope\Message\CompleteRoutingMessage;
use App\Envelope\Message\VoidEnvelopeMessage;
use App\Envelope\SigningMecahisms;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use ExportPortal\Bridge\Matrix\Notifier\Recipient\RoomType;
use ExportPortal\Bridge\Notifier\NotifierAwareTrait;
use ExportPortal\Bridge\Notifier\Recipient\Recipient;
use ExportPortal\Bridge\Notifier\Recipient\RightfulRecipient;
use Symfony\Component\Notifier\Notifier;
use Symfony\Component\Notifier\NotifierInterface;
use Throwable;

final class VoidEnvelope implements CommandInterface
{
    use HistoryAwareTrait;
    use NotifierAwareTrait;
    use EnvelopeAccessTrait;
    use ProviderResolverAwareTrait {
        ProviderResolverAwareTrait::setProviderResolver as setSigningProviderResolver;
        ProviderResolverAwareTrait::getProviderResolver as getSigningProviderResolver;
    }

    /**
     * The databse connection.
     */
    private Connection $connection;

    /**
     * The envelopes repository.
     */
    private Model $envelopesRepository;

    /**
     * The storage for the document files.
     */
    private FileStorageInterface $fileStorage;

    /**
     * Create instance of the command.
     */
    public function __construct(
        Model $envelopesRepository,
        FileStorageInterface $fileStorage,
        ?SigningProviderResolverInterface $signingProviderResolver = null,
        ?NotifierInterface $notifier = null
    ) {
        $this->notifier = $notifier ?? new Notifier([]);
        $this->connection = $envelopesRepository->getConnection();
        $this->fileStorage = $fileStorage;
        $this->envelopesRepository = $envelopesRepository;
        $this->setSigningProviderResolver($signingProviderResolver);
        $this->setHistoryRepository($envelopesRepository->getRelation('history')->getRelated());
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(VoidEnvelopeMessage $message)
    {
        $envelope = $this->findEnvelopeInStorage($envelopeId = $message->getEnvelopeId());
        $senderId = $message->getSenderId();

        //region Access check
        $this->assertEnvelopeIsVoidable($envelope);
        if (null !== $senderId) {
            $this->assertSenderIsEnvelopeOwner($senderId, $envelope);
        }
        //endregion Access check

        //region Void
        $this->connection->beginTransaction();

        try {
            // Update envelope
            $this->envelopesRepository->updateOne($envelopeId, [
                'status'                 => EnvelopeStatuses::VOIDED,
                'void_reason'            => $message->getReason(),
                'voided_at_date'         => new DateTimeImmutable(),
                'deleted_at_date'        => new DateTimeImmutable(), // For internal use
                'status_changed_at_date' => new DateTimeImmutable(),
            ]);

            // Remove envelope from remote signing service (if exists)
            if ($message->doRemoveExternals()) {
                $remoteEnvelopeId = $envelope['remote_envelope'] ?? null;
                $signingMechanism = $envelope['signing_mechanism'] ?? SigningMecahisms::NATIVE;
                if (
                    null !== $remoteEnvelopeId
                    && null !== $this->getSigningProviderResolver()
                    && null !== ($signingProvider = $this->getSigningProviderResolver()->resolve($signingMechanism))
                ) {
                    $signingProvider->removeEnvelope((string) $remoteEnvelopeId, $message->getReason());
                }
            }

            // Write history
            $this->addHistoryEvent(new HistoryEvent(HistoryEvent::VOID), $envelopeId, $senderId);

            //region Notify
            $orderId = $envelope['order_reference']['id_order'];
            $sender = \with($envelope['sender'] ?? null, fn ($sender) => null === $sender ? null : new Sender(
                $sender['id'],
                $sender['full_name'],
                $sender['legal_name'],
                $sender['group_type']
            ));

            if (EnvelopeStatuses::CREATED !== $envelope['status']) {
                $this->notifier->send(
                    new OrderDocumentEnvelopeNotification(Type::VOIDED_ENVELOPE, $orderId, $envelopeId, $envelope['display_title'], $sender, [
                        (string) SystemChannel::STORAGE(),
                        (string) SystemChannel::MATRIX(),
                    ]),
                    ...($envelope['recipients'] ?? new ArrayCollection())
                        ->map(fn (array $recipient) => (new Recipient((int) $recipient['id_user']))->withRoomType(RoomType::CARGO()))
                        ->toArray()
                );
            }
            if (!empty($message->getAccessRulesList())) {
                $this->notifier->send(
                    new OrderDocumentEnvelopeNotification(Type::VOIDED_ENVELOPE_FOR_MANAGER, $orderId, $envelopeId, $envelope['display_title'], $sender, [
                        (string) SystemChannel::STORAGE(),
                    ]),
                    new RightfulRecipient($message->getAccessRulesList())
                );
            }
            //endregion Notify

            // Complete the envelope routing
            (new CompleteRouting($this->envelopesRepository, $this->notifier))->__invoke(
                (new CompleteRoutingMessage($envelopeId, $envelope['workflow']['current_step']['id'] ?? null, false))->withAccessRulesList($message->getAccessRulesList())
            );

            $this->connection->commit();
        } catch (Throwable $e) {
            $this->connection->rollBack();

            throw new WriteException('Failed to update the records in the storage.', 0, $e);
        }
        //endregion Void
    }

    /**
     * Finds envelope in the repository.
     *
     * @throws NotFoundException if envelope is not found
     */
    private function findEnvelopeInStorage(?int $envelopeId): array
    {
        if (
            null === $envelopeId
            || null === $envelope = $this->envelopesRepository->findOneBy([
                'with'       => [
                    'workflow_steps as workflow',
                    'extended_sender as sender',
                    'order_reference',
                    'recipients',
                ],
                'conditions' => [
                    'id' => $envelopeId,
                ],
            ])
        ) {
            throw new NotFoundException(sprintf('The envelope with ID %s is not found', varToString($envelopeId)));
        }

        return $envelope;
    }
}
