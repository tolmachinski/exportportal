<?php

declare(strict_types=1);

namespace App\Envelope\Serial\Command;

use App\Common\Contracts\Notifier\SystemChannel;
use App\Common\Database\Model;
use App\Envelope\Bridge\Notifier\Notification\OrderDocumentEnvelopeNotification;
use App\Envelope\Bridge\Notifier\Sender;
use App\Envelope\Bridge\Notifier\Type;
use App\Envelope\EnvelopeAccessTrait;
use App\Envelope\EnvelopeStatuses;
use App\Envelope\Event\CompleteRouting;
use App\Envelope\Exception\WriteException;
use App\Envelope\HistoryAwareTrait;
use App\Envelope\HistoryEvent;
use App\Envelope\Message\CompleteRoutingMessage;
use App\Envelope\Message\DeclineSignedEnvelopeMessage;
use App\Envelope\RecipientAccessTrait;
use App\Envelope\RecipientStatuses;
use App\Envelope\Serial\SerialCommandTrait;
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

final class DeclineSignedEnvelope implements SerialCommandInterface
{
    use HistoryAwareTrait;
    use NotifierAwareTrait;
    use SerialCommandTrait;
    use EnvelopeAccessTrait;
    use RecipientAccessTrait;

    /**
     * The databse connection.
     */
    private Connection $connection;

    /**
     * The envelopes repository.
     */
    private Model $envelopesRepository;

    /**
     * The envelope workflow steps repository.
     */
    private Model $workflowRepository;

    /**
     * The envelope recipients repository.
     */
    private Model $recipientsRepository;

    /**
     * Create instance of the command.
     */
    public function __construct(Model $envelopesRepository, ?NotifierInterface $notifier = null)
    {
        $this->notifier = $notifier ?? new Notifier([]);
        $this->connection = $envelopesRepository->getConnection();
        $this->envelopesRepository = $envelopesRepository;
        $this->recipientsRepository = $envelopesRepository->getRelation('recipients')->getRelated();
        $this->workflowRepository = $envelopesRepository->getRelation('workflowSteps')->getRelated();
        $this->setHistoryRepository($envelopesRepository->getRelation('history')->getRelated());
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(DeclineSignedEnvelopeMessage $message)
    {
        //region Serial
        $senderId = $message->getSenderId();
        $envelope = $this->getEnvelopeFromStorage($envelopeId = $message->getEnvelopeId());
        $this->assertCurrentRoutingIsSerial(
            $currentRouting = $envelope['recipients_routing']['current_routing'] ?? new ArrayCollection(),
            $envelope['workflow']['current_step'] ?? null
        );
        $this->assertSenderIsEnvelopeOwner($senderId, $envelope);
        $this->assertCanDeclineOrConfirmSignedEnvelope($senderId, $envelope);
        //endregion Serial

        //region Access check
        $this->assertRecipientIsSigner($currentRecipient = $currentRouting->first() ?: []);
        //endregion Access check

        //region Decline
        $this->connection->beginTransaction();

        try {
            // Update envelope
            $this->envelopesRepository->updateOne($envelopeId, [
                'status'                 => EnvelopeStatuses::DECLINED,
                'declined_at_date'       => new DateTimeImmutable(),
                'status_changed_at_date' => new DateTimeImmutable(),
            ]);

            // Update recipient
            $this->recipientsRepository->updateOne((int) $currentRecipient['id'], [
                'status'             => RecipientStatuses::DECLINED,
                'decline_reason'     => $message->getDeclineReason(),
                'declined_at_date'   => new DateTimeImmutable(),
                'declined_by_sender' => true,
            ]);

            // Write history
            $this->addHistoryEvent(new HistoryEvent(HistoryEvent::DECLINE_SIGNATURE), $envelopeId, $senderId);

            //region Notify
            $orderId = $envelope['order_reference']['id_order'];
            $sender = \with($envelope['sender'] ?? null, fn ($sender) => null === $sender ? null : new Sender(
                $sender['id'],
                $sender['full_name'],
                $sender['legal_name'],
                $sender['group_type']
            ));

            $this->notifier->send(
                new OrderDocumentEnvelopeNotification(Type::DECLINED_SIGNED_ENVELOPE, $orderId, $envelopeId, $envelope['display_title'], $sender, [
                    (string) SystemChannel::STORAGE(),
                    (string) SystemChannel::MATRIX(),
                ]),
                (new Recipient((int) $currentRecipient['id_user']))->withRoomType(RoomType::CARGO())
            );
            if (!empty($message->getAccessRulesList())) {
                $this->notifier->send(
                    new OrderDocumentEnvelopeNotification(Type::DECLINED_SIGNED_ENVELOPE_FOR_MANAGER, $orderId, $envelopeId, $envelope['display_title'], $sender, [
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
        //endregion Decline
    }
}
