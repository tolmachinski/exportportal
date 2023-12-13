<?php

declare(strict_types=1);

namespace App\Envelope\Command;

use App\Common\Contracts\Notifier\SystemChannel;
use App\Common\Database\Model;
use App\Common\Exceptions\NotFoundException;
use App\Envelope\Bridge\Notifier\Notification\OrderDocumentEnvelopeNotification;
use App\Envelope\Bridge\Notifier\Sender;
use App\Envelope\Bridge\Notifier\Type;
use App\Envelope\EnvelopeAccessTrait;
use App\Envelope\Exception\EnvelopeException;
use App\Envelope\Exception\WriteEnvelopeException;
use App\Envelope\HistoryAwareTrait;
use App\Envelope\HistoryEvent;
use App\Envelope\Message\UpdateEnvelopeDisplayInformationMessage;
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

final class UpdateEnvelopeDisplayInformation implements CommandInterface
{
    use HistoryAwareTrait;
    use NotifierAwareTrait;
    use EnvelopeAccessTrait;

    /**
     * The envelopes repository.
     */
    private Model $envelopesRepository;

    /**
     * The databse connection.
     */
    private Connection $connection;

    /**
     * Create instance of the command.
     */
    public function __construct(Model $envelopesRepository, ?NotifierInterface $notifier = null)
    {
        $this->notifier = $notifier ?? new Notifier([]);
        $this->connection = $envelopesRepository->getConnection();
        $this->envelopesRepository = $envelopesRepository;
        $this->setHistoryRepository($envelopesRepository->getRelation('history')->getRelated());
    }

    /**
     * {@inheritdoc}
     *
     * @throws WriteEnvelopeException if failed to write the envelope draft
     */
    public function __invoke(UpdateEnvelopeDisplayInformationMessage $message)
    {
        $originalEnvelope = $this->findEnvelopeInStorage($envelopeId = $message->getEnvelopeId());

        //region Access check
        $this->assertSenderIsEnvelopeOwner($message->getSenderId(), $originalEnvelope);
        $this->assertCanEditEnvelopeDisplayInfo($originalEnvelope);
        //endregion Access check

        //region Collect Envelope
        $envelope = [
            'display_title'       => $message->getTitle(),
            'display_type'        => $message->getType(),
            'display_description' => $message->getDescription(),
        ];
        foreach (['display_type', 'display_title', 'display_description'] as $key) {
            if (($originalEnvelope[$key] ?? null) !== ($envelope[$key] ?? null)) {
                $envelope['dispaly_info_updated_at_date'] = new DateTimeImmutable();

                break;
            }
        }
        //endregion Collect Envelope

        //region Update
        $this->connection->beginTransaction();

        try {
            //region Write to Storage
            if (!(bool) $this->envelopesRepository->updateOne($envelopeId, $envelope)) {
                throw new WriteEnvelopeException('Failed to write the envelope into database', 0, $e ?? null);
            }
            //endregion Write to Storage

            //region Write history
            $this->addHistoryEvent(new HistoryEvent(HistoryEvent::EDIT_DESCRIPTION), $envelopeId, $message->getSenderId());
            //endregion Write history

            //region Notify
            $orderId = $originalEnvelope['order_reference']['id_order'];
            $sender = \with($originalEnvelope['sender'] ?? null, fn ($sender) => null === $sender ? null : new Sender(
                $sender['id'],
                $sender['full_name'],
                $sender['legal_name'],
                $sender['group_type']
            ));

            $this->notifier->send(
                new OrderDocumentEnvelopeNotification(Type::UPDATED_ENVELOPE, $orderId, $envelopeId, $envelope['display_title'], $sender, [
                    (string) SystemChannel::STORAGE(),
                    (string) SystemChannel::MATRIX(),
                ]),
                ...($originalEnvelope['recipients'] ?? new ArrayCollection())
                    ->map(fn (array $recipient) => (new Recipient((int) $recipient['id_user']))->withRoomType(RoomType::CARGO()))
                    ->toArray()
            );
            if (!empty($message->getAccessRulesList())) {
                $this->notifier->send(
                    new OrderDocumentEnvelopeNotification(Type::UPDATED_ENVELOPE_FOR_MANAGER, $orderId, $envelopeId, $envelope['display_title'], $sender, [
                        (string) SystemChannel::STORAGE(),
                    ]),
                    new RightfulRecipient($message->getAccessRulesList())
                );
            }
            //endregion Notify

            $this->connection->commit();
        } catch (Throwable $e) {
            $this->connection->rollBack();
            if ($e instanceof EnvelopeException) {
                throw $e;
            }

            throw new EnvelopeException('Failed to update envelope display information', 0, $e);
        }
        //endregion Update
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
            || null === $envelope = $this->envelopesRepository->findOne($envelopeId, [
                'with' => ['extended_sender as sender', 'recipients', 'order_reference'],
            ])
        ) {
            throw new NotFoundException(sprintf('The envelope with ID %s is not found', varToString($envelopeId)));
        }

        return $envelope;
    }
}
