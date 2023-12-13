<?php

declare(strict_types=1);

namespace App\Envelope\Event;

use App\Common\Contracts\Notifier\SystemChannel;
use App\Common\Database\Model;
use App\Envelope\Bridge\Notifier\Notification\OrderDocumentEnvelopeNotification;
use App\Envelope\Bridge\Notifier\Sender;
use App\Envelope\Bridge\Notifier\Type;
use App\Envelope\Command\CommandInterface;
use App\Envelope\EnvelopeStatuses;
use App\Envelope\Exception\WriteException;
use App\Envelope\HistoryAwareTrait;
use App\Envelope\HistoryEvent;
use App\Envelope\Message\SendEnvelopeToRecipientsMessage;
use App\Envelope\RecipientStatuses;
use App\Envelope\RecipientTypes;
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

final class SendEnvelopeToRecipients implements CommandInterface
{
    use HistoryAwareTrait;
    use NotifierAwareTrait;

    /**
     * The envelopes repository.
     */
    private Model $envelopesRepository;

    /**
     * The envelope recipients repository.
     */
    private Model $recipientsRepository;

    /**
     * The databse connection.
     */
    private Connection $connection;

    /**
     * Creates instance of the command.
     */
    public function __construct(Model $envelopesRepository, ?NotifierInterface $notifier = null)
    {
        $this->notifier = $notifier ?? new Notifier([]);
        $this->connection = $envelopesRepository->getConnection();
        $this->envelopesRepository = $envelopesRepository;
        $this->recipientsRepository = $envelopesRepository->getRelation('recipients')->getRelated();
        $this->setHistoryRepository($envelopesRepository->getRelation('history')->getRelated());
    }

    /**
     * Runs the command.
     */
    public function __invoke(SendEnvelopeToRecipientsMessage $message): void
    {
        // TODO: do some costly operation that sends the information about envelope to the user (for example, email etc.)

        try {
            $this->connection->beginTransaction();

            // Update envelope
            $this->envelopesRepository->updateOne($message->getEnvelopeId(), [
                'status'                   => EnvelopeStatuses::SENT,
                'sent_at_date'             => new DateTimeImmutable(),
                'sent_original_at_date'    => $envelope['sent_original_at_date'] ?? new DateTimeImmutable(),
                'status_changed_at_date'   => new DateTimeImmutable(),
            ]);

            // Update recipients
            if (!empty($recipients = $message->getRecipients())) {
                $this->recipientsRepository->updateMany(
                    [
                        'status'       => RecipientStatuses::SENT,
                        'sent_at_date' => new DateTimeImmutable(),
                    ],
                    [
                        'conditions' => [
                            'ids' => \array_column($recipients, 'id'),
                        ],
                    ]
                );
            }

            // Write history
            $this->addHistoryEvent(new HistoryEvent(HistoryEvent::SEND), $message->getEnvelopeId(), $message->getUserId());

            //region Notify
            $envelope = $this->envelopesRepository->findOne($envelopeId = $message->getEnvelopeId(), ['with' => ['order_reference', 'extended_sender as sender', 'recipients']]);
            $recipients = new ArrayCollection($envelope['recipients']->toArray());
            $orderId = $envelope['order_reference']['id_order'];
            // $signers = $recipients->filter(fn (array $recipient) => RecipientTypes::SIGNER === $recipient['type']);
            // $viewers = $recipients->filter(fn (array $recipient) => RecipientTypes::VIEWER === $recipient['type']);
            $sender = \with($envelope['sender'] ?? null, fn ($sender) => null === $sender ? null : new Sender(
                $sender['id'],
                $sender['full_name'],
                $sender['legal_name'],
                $sender['group_type']
            ));
            // Send notification to the users
            if (!$message->isSilent()) {
                $this->notifier->send(
                    new OrderDocumentEnvelopeNotification(Type::SENT_ENVELOPE, $orderId, $envelopeId, $envelope['display_title'], $sender, [
                        (string) SystemChannel::STORAGE(),
                        (string) SystemChannel::MATRIX(),
                    ]),
                    ...$recipients->map(fn (array $recipient) => (new Recipient((int) $recipient['id_user']))->withRoomType(RoomType::CARGO()))->toArray()
                );
                if (!empty($message->getAccessRulesList())) {
                    $this->notifier->send(
                        new OrderDocumentEnvelopeNotification(Type::SENT_ENVELOPE_FOR_MANAGER, $orderId, $envelopeId, $envelope['display_title'], $sender, [
                            (string) SystemChannel::STORAGE(),
                        ]),
                        new RightfulRecipient($message->getAccessRulesList())
                    );
                }
            }
            //endregion Notify

            $this->connection->commit();
        } catch (Throwable $e) {
            $this->connection->rollBack();

            throw new WriteException('Failed to update the records in the storage.', 0, $e);
        }
        //endregion Updates
    }

    /**
     * Determines if the operation for the recipient must be made in the background.
     */
    private function isBackgroundRecipientOperation(string $recipientType): bool
    {
        return in_array($recipientType, [RecipientTypes::OPERATOR]);
    }
}
