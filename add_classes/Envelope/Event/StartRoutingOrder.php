<?php

declare(strict_types=1);

namespace App\Envelope\Event;

use App\Common\Database\Model;
use App\Envelope\Command\CommandInterface;
use App\Envelope\EnvelopeAccessTrait;
use App\Envelope\EnvelopeWorkflowStatuses;
use App\Envelope\Exception\WriteException;
use App\Envelope\Message\BindRecipientsToWorkflowMessage;
use App\Envelope\Message\StartRoutingOrderMessage;
use App\Envelope\WorkflowStepStatuses;
use App\Envelope\WorkflowStepTypes;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Throwable;

final class StartRoutingOrder implements CommandInterface
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
    public function __construct(Model $envelopesRepository)
    {
        $this->connection = $envelopesRepository->getConnection();
        $this->workflowRepository = $envelopesRepository->getRelation('workflowSteps')->getRelated();
        $this->envelopesRepository = $envelopesRepository;
    }

    /**
     * Runs the command.
     */
    public function __invoke(StartRoutingOrderMessage $message): void
    {
        if (null === $routingOrder = $message->getRoutingOrder()) {
            return;
        }

        //region Updates
        $envelopeId = $message->getEnvelopeId();
        $recipients = $message->getRecipients();
        $lastWorkflowStepId = $message->getLastWorkfllowStep();

        $this->connection->beginTransaction();

        try {
            // Complete previous step (if exists)
            if (null !== $lastWorkflowStepId) {
                $this->workflowRepository->updateOne($lastWorkflowStepId, [
                    'status'            => WorkflowStepStatuses::COMPLETED,
                    'completed_at_date' => new DateTimeImmutable(),
                ]);
            }

            // Create first workflow step.
            $workflowStepId = $this->workflowRepository->insertOne([
                'id_envelope' => $envelopeId,
                'step_order'  => $routingOrder,
                'uuid'        => $workflowStepUuid = Uuid::uuid6(),
                'status'      => WorkflowStepStatuses::IN_PROGRESS,
                'action'      => count($recipients) > 1
                    ? WorkflowStepTypes::PARALLEL_RECIPIENT_ROUTING
                    : WorkflowStepTypes::RECIPIENT_ROUTING,
            ]);

            // Bind recipients to workflow
            (new BindRecipientsToWorkflow($this->workflowRepository->getRelation('recipients')->getRelated()))->__invoke(
                new BindRecipientsToWorkflowMessage(
                    (int) $workflowStepId,
                    $recipients
                )
            );

            // Initial envelope update data
            $envelopeUpdate = [
                // Set intial routing order
                'current_routing_order'    => $routingOrder,
                // Set first workflow step
                'current_workflow_step'    => $workflowStepUuid,
                'workflow_status'          => EnvelopeWorkflowStatuses::IN_PROGRESS,
            ];

            // Update envelope
            $this->envelopesRepository->updateOne($envelopeId, $envelopeUpdate);
            $this->connection->commit();
        } catch (Throwable $e) {
            $this->connection->rollBack();

            throw new WriteException('Failed to update the records in the storage.', 0, $e);
        }
        //endregion Updates
    }
}
