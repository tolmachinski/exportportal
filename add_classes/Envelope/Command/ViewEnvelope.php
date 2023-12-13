<?php

declare(strict_types=1);

namespace App\Envelope\Command;

use App\Common\Contracts\Notifier\SystemChannel;
use App\Common\Database\Model;
use App\Common\Exceptions\NotFoundException;
use App\Envelope\Bridge\Notifier\Notification\OrderDocumentEnvelopeNotification;
use App\Envelope\Bridge\Notifier\Sender;
use App\Envelope\Bridge\Notifier\Type;
use App\Envelope\Event\CompleteRouting;
use App\Envelope\Event\StartRoutingOrder;
use App\Envelope\Exception\WriteException;
use App\Envelope\HistoryAwareTrait;
use App\Envelope\HistoryEvent;
use App\Envelope\Message\CompleteRoutingMessage;
use App\Envelope\Message\DeliverEnvelopeMessage;
use App\Envelope\Message\StartRoutingOrderMessage;
use App\Envelope\Message\ViewEnvelopeMessage;
use App\Envelope\RecipientAccessTrait;
use App\Envelope\RecipientStatuses;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Connection;
use ExportPortal\Bridge\Matrix\Notifier\Recipient\RoomType;
use ExportPortal\Bridge\Notifier\NotifierAwareTrait;
use ExportPortal\Bridge\Notifier\Recipient\Recipient;
use ExportPortal\Bridge\Notifier\Recipient\RightfulRecipient;
use Symfony\Component\Notifier\Notifier;
use Symfony\Component\Notifier\NotifierInterface;
use Throwable;

final class ViewEnvelope implements CommandInterface
{
    use HistoryAwareTrait;
    use NotifierAwareTrait;
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
    public function __invoke(ViewEnvelopeMessage $message)
    {
        $envelope = $this->getEnvelopeFromStorage($envelopeId = $message->getEnvelopeId());

        //region Access check
        $routing = $envelope['recipients_routing'];
        /** @var null|Collection $currentRouting */
        $currentRouting = $routing['current_routing'] ?? null;
        $recipientId = $message->getRecipientId();
        /** @var null|array $currentRecipient */
        $currentRecipient = $currentRouting->filter(fn (array $recipient) => $recipientId === (int) $recipient['id_user'])->first() ?: null;

        $this->assertSenderIsCurrentRecipient($recipientId, $currentRouting);
        $this->assertRecipientCanView($currentRecipient);
        //endregion Access check

        //region View
        try {
            // Mark recipient as delivered, if needed
            (new DeliverEnvelopeToRecipient($this->envelopesRepository))->__invoke(
                new DeliverEnvelopeMessage($envelopeId, $recipientId, $envelope['current_routing_order'])
            );
        } catch (Throwable $e) {
            // TODO: log this exception
        }

        $this->connection->beginTransaction();

        try {
            //region Update recipient
            $this->recipientsRepository->updateOne((int) $currentRecipient['id'], [
                'status'            => RecipientStatuses::COMPLETED,
                'completed_at_date' => new DateTimeImmutable(),
            ]);
            //endregion Update recipient

            // Write history
            $this->addHistoryEvent(new HistoryEvent(HistoryEvent::VIEW), $envelopeId, $recipientId);

            //region Notify
            $sender = new Sender($currentRecipient['id_user'], $currentRecipient['full_name'], $currentRecipient['legal_name'], $currentRecipient['group_type']);
            $orderId = $envelope['order_reference']['id_order'];

            if (isset($envelope['id_sender'])) {
                $this->notifier->send(
                    new OrderDocumentEnvelopeNotification(Type::VIEWED_ENVELOPE, $orderId, $envelopeId, $envelope['display_title'], $sender, [
                        (string) SystemChannel::STORAGE(),
                        (string) SystemChannel::MATRIX(),
                    ]),
                    (new Recipient((int) $envelope['id_sender']))->withRoomType(RoomType::CARGO())
                );
            }
            if (!empty($message->getAccessRulesList())) {
                $this->notifier->send(
                    new OrderDocumentEnvelopeNotification(Type::VIEWED_ENVELOPE_FOR_MANAGER, $orderId, $envelopeId, $envelope['display_title'], $sender, [
                        (string) SystemChannel::STORAGE(),
                    ]),
                    new RightfulRecipient($message->getAccessRulesList())
                );
            }
            //endregion Notify

            //region Roll next
            $nextRouting = $routing['next_routing'] ?? null;
            $nextRoutingOrderId = $routing['next_routing_order'] ?? null;
            $currentWorkflowStepId = $envelope['workflow']['current_step']['id'] ?? null;
            if (null === $nextRouting) {
                // Complete envelope if nothing on the next step
                (new CompleteRouting($this->envelopesRepository, $this->notifier))->__invoke(
                    (new CompleteRoutingMessage(
                        $envelopeId,
                        $currentWorkflowStepId,
                        true,
                        \array_filter(
                            [
                                $envelope['id_sender'],
                                ...$routing['recipients']->map(fn (array $recipient) => $recipient['id_user'])->toArray(),
                            ],
                            fn ($id) => null !== $id
                        )
                    ))->withAccessRulesList($message->getAccessRulesList())
                );
            } else {
                // Else start new routing
                (new StartRoutingOrder($this->envelopesRepository))->__invoke(
                    new StartRoutingOrderMessage(
                        $envelopeId,
                        $nextRoutingOrderId,
                        $currentWorkflowStepId,
                        \array_column($nextRouting->toArray(), 'id')
                    )
                );
            }
            //endregion Roll next

            $this->connection->commit();
        } catch (Throwable $e) {
            $this->connection->rollBack();

            throw new WriteException('Failed to update the records in the storage.', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getEnvelopeFromStorage(?int $envelopeId): array
    {
        if (
            null === $envelopeId
            || null === $envelope = $this->envelopesRepository->findOne($envelopeId, [
                'with'       => [
                    'extended_recipients as recipients_routing',
                    'workflow_steps as workflow',
                    'order_reference',
                ],
            ])
        ) {
            throw new NotFoundException(sprintf('The envelope with ID %s is not found', varToString($envelopeId)));
        }

        return $envelope;
    }
}
