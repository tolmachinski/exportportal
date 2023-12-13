<?php

declare(strict_types=1);

namespace App\Envelope\Event;

use App\Common\Contracts\Notifier\SystemChannel;
use App\Common\Database\Model;
use App\Envelope\Bridge\Notifier\Notification\OrderDocumentEnvelopeNotification;
use App\Envelope\Bridge\Notifier\Type;
use App\Envelope\Command\CommandInterface;
use App\Envelope\EnvelopeAccessTrait;
use App\Envelope\EnvelopeStatuses;
use App\Envelope\EnvelopeWorkflowStatuses;
use App\Envelope\Exception\WriteException;
use App\Envelope\Message\CompleteRoutingMessage;
use App\Envelope\WorkflowStepStatuses;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use ExportPortal\Bridge\Matrix\Notifier\Recipient\RoomType;
use ExportPortal\Bridge\Notifier\Recipient\Recipient;
use ExportPortal\Bridge\Notifier\Recipient\RightfulRecipient;
use Symfony\Component\Notifier\Notifier;
use Symfony\Component\Notifier\NotifierInterface;
use Throwable;

final class CompleteRouting implements CommandInterface
{
    use EnvelopeAccessTrait;

    /**
     * The envelopes repository.
     */
    private Model $envelopesRepository;

    /**
     * The envelope workflow steps repository.
     */
    private Model $workflowRepository;

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
        $this->workflowRepository = $envelopesRepository->getRelation('workflowSteps')->getRelated();
        $this->envelopesRepository = $envelopesRepository;
    }

    /**
     * Runs the command.
     */
    public function __invoke(CompleteRoutingMessage $message): void
    {
        $this->connection->beginTransaction();

        try {
            $envelopeUpdate = [
                // End workflow
                'workflow_status'       => EnvelopeWorkflowStatuses::COMPLETED,
                'workflow_completed_at' => new DateTimeImmutable(),
            ];
            if (!$message->skipStatusUpdate()) {
                $envelopeUpdate['status'] = EnvelopeStatuses::COMPLETED;
                $envelopeUpdate['completed_at_date'] = new DateTimeImmutable();
                $envelopeUpdate['status_changed_at_date'] = new DateTimeImmutable();
            }

            // Complete envelope
            $this->envelopesRepository->updateOne($message->getEnvelopeId(), $envelopeUpdate);

            // Update workflow step
            $currentWorkflowStep = $message->getLastWorkfllowStep();
            if (null !== $currentWorkflowStep) {
                $this->workflowRepository->updateOne($currentWorkflowStep, [
                    'status'            => WorkflowStepStatuses::COMPLETED,
                    'completed_at_date' => new DateTimeImmutable(),
                ]);
            }

            $this->connection->commit();
        } catch (Throwable $e) {
            $this->connection->rollBack();

            throw new WriteException('Failed to update the records in the storage.', 0, $e);
        }

        if (!empty($message->getNotificationsRecipients())) {
            $this->notifier->send(
                new OrderDocumentEnvelopeNotification(Type::COMPLETED_ENVELOPE, null, $message->getEnvelopeId(), null, null, [
                    (string) SystemChannel::STORAGE(),
                    (string) SystemChannel::MATRIX(),
                ]),
                ...\array_map(fn (int $id) => (new Recipient((int) $id))->withRoomType(RoomType::CARGO()), $message->getNotificationsRecipients())
            );
            if (!empty($message->getAccessRulesList())) {
                $this->notifier->send(
                    new OrderDocumentEnvelopeNotification(Type::COMPLETED_ENVELOPE_FOR_MANAGER, null, $message->getEnvelopeId(), null, null, [
                        (string) SystemChannel::STORAGE(),
                    ]),
                    new RightfulRecipient($message->getAccessRulesList())
                );
            }
        }
    }
}
