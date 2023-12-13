<?php

declare(strict_types=1);

namespace App\Envelope\Command;

use App\Common\Database\Model;
use App\Envelope\EnvelopeStatuses;
use App\Envelope\Exception\WriteException;
use App\Envelope\HistoryAwareTrait;
use App\Envelope\HistoryEvent;
use App\Envelope\Message\DeliverEnvelopeMessage;
use App\Envelope\RecipientStatuses;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Connection;
use Throwable;

final class DeliverEnvelopeToRecipient implements CommandInterface
{
    use HistoryAwareTrait;

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
    public function __construct(Model $envelopesRepository)
    {
        $this->connection = $envelopesRepository->getConnection();
        $this->envelopesRepository = $envelopesRepository;
        $this->recipientsRepository = $envelopesRepository->getRelation('recipients')->getRelated();
        $this->setHistoryRepository($envelopesRepository->getRelation('history')->getRelated());
    }

    /**
     * Runs the command.
     */
    public function __invoke(DeliverEnvelopeMessage $message): void
    {
        $senderId = $message->getSenderId();
        $routingOrder = $message->getRoutingOrder();
        $recipients = $this->findRecipientsinStorage($message->getEnvelopeId());
        $targetRecipient = $recipients
            ->filter(
                fn (array $recipient) => $senderId === $recipient['id_user']
                    && $routingOrder === $recipient['routing_order']
                    && RecipientStatuses::SENT === $recipient['status']
            )
            ->first() ?: null
        ;

        if (null === $targetRecipient) {
            return;
        }

        $recipientId = $targetRecipient['id'];

        try {
            $this->connection->beginTransaction();

            // Update recipients
            if (!empty($recipients)) {
                $this->recipientsRepository->updateOne($recipientId, [
                    'status'           => RecipientStatuses::DELIVERED,
                    'delivery_at_date' => new DateTimeImmutable(),
                ]);
            }

            if (
                $recipients
                    ->filter(
                        fn (array $recipient) => $routingOrder === $recipient['routing_order']
                            && RecipientStatuses::SENT === $recipient['status']
                    )
                    ->isEmpty()
            ) {
                // Update envelope
                $this->envelopesRepository->updateOne($message->getEnvelopeId(), [
                    'status'                   => EnvelopeStatuses::DELIVERED,
                    'status_changed_at_date'   => new DateTimeImmutable(),
                ]);
            }

            // Write history
            $this->addHistoryEvent(new HistoryEvent(HistoryEvent::DELIVER), $message->getEnvelopeId(), $senderId);

            $this->connection->commit();
        } catch (Throwable $e) {
            $this->connection->rollBack();

            throw new WriteException('Failed to update the records in the storage.', 0, $e);
        }
        //endregion Updates
    }

    /**
     * Finds recipients in the repository.
     */
    private function findRecipientsinStorage(int $envelopeId): Collection
    {
        return new ArrayCollection(
            $this->recipientsRepository->findAllBy([
                'conditions' => [
                    'envelope' => $envelopeId,
                ],
            ])
        );
    }
}
