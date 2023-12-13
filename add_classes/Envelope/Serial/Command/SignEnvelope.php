<?php

declare(strict_types=1);

namespace App\Envelope\Serial\Command;

use App\Common\Contracts\Notifier\SystemChannel;
use App\Common\Database\Model;
use App\Common\Exceptions\NotFoundException;
use App\Envelope\Bridge\Notifier\Notification\OrderDocumentEnvelopeNotification;
use App\Envelope\Bridge\Notifier\Sender;
use App\Envelope\Bridge\Notifier\Type;
use App\Envelope\EnvelopeStatuses;
use App\Envelope\EnvelopeTypes;
use App\Envelope\Event\CompleteRouting;
use App\Envelope\Event\HideOutdatedDocuments;
use App\Envelope\Event\StartRoutingOrder;
use App\Envelope\Event\StoreTemporaryFiles;
use App\Envelope\Exception\WriteException;
use App\Envelope\File\StorageInterface as FileStorageInterface;
use App\Envelope\HistoryAwareTrait;
use App\Envelope\HistoryEvent;
use App\Envelope\Message\CompleteRoutingMessage;
use App\Envelope\Message\HideOutdatedDocumentsMessage;
use App\Envelope\Message\SignEnvelopeMessage;
use App\Envelope\Message\StartRoutingOrderMessage;
use App\Envelope\Message\StoreTemporaryFilesMessage;
use App\Envelope\RecipientAccessTrait;
use App\Envelope\RecipientStatuses;
use App\Envelope\Serial\SerialCommandTrait;
use App\Envelope\WorkflowStepStatuses;
use App\Envelope\WorkflowStepTypes;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Connection;
use ExportPortal\Bridge\Matrix\Notifier\Recipient\RoomType;
use ExportPortal\Bridge\Notifier\NotifierAwareTrait;
use ExportPortal\Bridge\Notifier\Recipient\Recipient;
use ExportPortal\Bridge\Notifier\Recipient\RightfulRecipient;
use Symfony\Component\Notifier\Notifier;
use Symfony\Component\Notifier\NotifierInterface;
use Throwable;

final class SignEnvelope implements SerialCommandInterface
{
    use HistoryAwareTrait;
    use NotifierAwareTrait;
    use SerialCommandTrait;
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
     * The envelope documents repository.
     */
    private Model $documentsRepository;

    /**
     * The storage for the document files.
     */
    private FileStorageInterface $fileStorage;

    /**
     * Create instance of the command.
     */
    public function __construct(Model $envelopesRepository, FileStorageInterface $fileStorage, ?NotifierInterface $notifier = null)
    {
        $this->notifier = $notifier ?? new Notifier([]);
        $this->connection = $envelopesRepository->getConnection();
        $this->fileStorage = $fileStorage;
        $this->envelopesRepository = $envelopesRepository;
        $this->recipientsRepository = $envelopesRepository->getRelation('recipients')->getRelated();
        $this->documentsRepository = $envelopesRepository->getRelation('documents')->getRelated();
        $this->workflowRepository = $envelopesRepository->getRelation('workflowSteps')->getRelated();
        $this->setHistoryRepository($envelopesRepository->getRelation('history')->getRelated());
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(SignEnvelopeMessage $message)
    {
        $envelope = $this->getEnvelopeFromStorage($envelopeId = $message->getEnvelopeId());

        //region Serial
        /** @var null|Collection $currentRouting */
        $currentRouting = $envelope['recipients_routing']['current_routing'] ?? null;
        /** @var null|Collection $nextRouting */
        $nextRouting = $envelope['recipients_routing']['next_routing'] ?? null;
        $this->assertCurrentRoutingIsSerial(
            $currentRouting ?? new ArrayCollection(),
            $currentWorkflowStep = $envelope['workflow']['current_step'] ?? null
        );
        //endregion Serial

        //region Access check
        $senderId = $message->getSenderId();
        /** @var null|array $currentRecipient */
        $currentRecipient = $currentRouting->filter(fn (array $recipient) => $senderId === (int) $recipient['id_user'])->first() ?: null;

        $this->assertSenderIsCurrentRecipient($senderId = $message->getSenderId(), $currentRouting);
        $this->assertRecipientCanSignAndDecline($currentRecipient);
        //endregion Access check

        //Get parent document for signing
        $parentDocument = $this->getDocumentsFromStorage($envelopeId, $currentRecipient['id']);

        //region Sign
        $this->connection->beginTransaction();

        try {
            // Create envelope documents
            // If next routing step exists, we need to take recipients from it
            // and bind them to the new documents after it is created.
            $assignees = [$envelope['id_sender']];
            $boundRecipients = [$currentRecipient['id']];
            /** @var Collection $recipients */
            $recipients = $envelope['recipients_routing']['recipients'] ?? new ArrayCollection();
            foreach ($recipients as $recipient) {
                if ((int) $currentRecipient['id'] === (int) $recipient['id']) {
                    continue;
                }

                $assignees[] = $recipient['id_user'];
                $boundRecipients[] = $recipient['id'];
            }

            // if (null !== $nextRouting) {
            //     // Add users ID from the next routing and made them unique.
            //     // In such way, after export proper permissions for files in storage will be create.
            //     $assignees = \array_unique(\array_merge($assignees, \array_column($nextRouting->toArray(), 'id_user')));

            //     // Add recipients from the next
            //     $boundRecipients = \array_merge(
            //         $boundRecipients,
            //         $nextRouting->map(
            //             fn (array $recipient) => array_merge(
            //                 \array_intersect_key($recipient, ['id' => null, 'type' => null]),
            //             )
            //         )->toArray()
            //     );
            // }

            // Hide outdated documents for recipients
            (new HideOutdatedDocuments($this->documentsRepository))->__invoke(
                new HideOutdatedDocumentsMessage(
                    $envelopeId,
                    \array_unique($boundRecipients),
                )
            );

            // Create new documents
            (new StoreTemporaryFiles($this->documentsRepository, $this->fileStorage))->__invoke(
                new StoreTemporaryFilesMessage(
                    $envelopeId,
                    $message->getSenderId(),
                    \array_map(fn (string $id) => \base64_decode($id), $message->getTemporaryFiles()),
                    \array_unique(\array_filter($assignees, fn ($id) => null !== $id)),
                    \array_unique($boundRecipients),
                    'signed',
                    false,
                    $parentDocument['id']
                )
            );

            // Update recipient
            $this->recipientsRepository->updateOne((int) $currentRecipient['id'], [
                'status'         => RecipientStatuses::SIGNED,
                'signed_at_date' => new DateTimeImmutable(),
            ]);

            // Update workflow step
            if (null !== $currentWorkflowStep ?? WorkflowStepTypes::RECIPIENT_ROUTING === $currentWorkflowStep['action']) {
                $this->workflowRepository->updateOne((int) $currentWorkflowStep['id'], [
                    'status'            => WorkflowStepStatuses::COMPLETED,
                    'completed_at_date' => new DateTimeImmutable(),
                ]);
            }

            // If there is no next routing, mark envelope as signed
            if (null === $nextRouting) {
                $this->envelopesRepository->updateOne($envelopeId, [
                    'status'                 => EnvelopeStatuses::SIGNED,
                    'status_changed_at_date' => new DateTimeImmutable(),
                ]);
            }

            // Write history
            $this->addHistoryEvent(new HistoryEvent(HistoryEvent::SIGN), $message->getEnvelopeId(), $message->getSenderId());

            //region Notify
            $sender = new Sender($currentRecipient['id_user'], $currentRecipient['full_name'], $currentRecipient['legal_name'], $currentRecipient['group_type']);
            $orderId = $envelope['order_reference']['id_order'];

            if (isset($envelope['id_sender'])) {
                $this->notifier->send(
                    new OrderDocumentEnvelopeNotification(Type::SIGNED_ENVELOPE, $orderId, $envelopeId, $envelope['display_title'], $sender, [
                        (string) SystemChannel::STORAGE(),
                        (string) SystemChannel::MATRIX(),
                    ]),
                    (new Recipient((int) $envelope['id_sender']))->withRoomType(RoomType::CARGO())
                );
            }
            if (!empty($message->getAccessRulesList())) {
                $this->notifier->send(
                    new OrderDocumentEnvelopeNotification(Type::SIGNED_ENVELOPE_FOR_MANAGER, $orderId, $envelopeId, $envelope['display_title'], $sender, [
                        (string) SystemChannel::STORAGE(),
                    ]),
                    new RightfulRecipient($message->getAccessRulesList())
                );
            }
            //endregion Notify

            // If envelope is internal, then we need to pass the confirmation stage
            if (EnvelopeTypes::INTERNAL === $envelope['type']) {
                //region Roll next
                $nextRoutingOrderId = $envelope['recipients_routing']['next_routing_order'] ?? null;
                $currentWorkflowStepId = $currentWorkflowStep['id'] ?? null;
                if (null === $nextRouting) {
                    // Complete envelope if nothing on the next step
                    (new CompleteRouting($this->envelopesRepository))->__invoke(
                        (new CompleteRoutingMessage($envelopeId, $currentWorkflowStepId, true))->withAccessRulesList($message->getAccessRulesList())
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
            }

            $this->connection->commit();
        } catch (Throwable $e) {
            $this->connection->rollBack();

            throw new WriteException('Failed to update the records in the storage.', 0, $e);
        }
        //endregion Sign
    }

    /**
     * Get document from storage.
     */
    protected function getDocumentsFromStorage(int $envelopeId, int $recipientId): array
    {
        if (
            null === $document = $this->documentsRepository->findOneBy([
                'order'      => ['id' => 'DESC'],
                'conditions' => [
                    'envelope' => $envelopeId,
                ],
            ])
        ) {
            throw new NotFoundException(
                sprintf(
                    'The document for recipient %s and envelope %s is not found',
                    varToString($recipientId),
                    varToString($envelopeId)
                )
            );
        }

        return $document;
    }
}
