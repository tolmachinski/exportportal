<?php

declare(strict_types=1);

namespace App\Envelope\Event;

use App\Common\Database\Model;
use App\Envelope\Command\CommandInterface;
use App\Envelope\Exception\RemoveWorkflowException;
use App\Envelope\Message\RemoveEnvelopeComponentsMessage;
use Exception;

final class RemoveEnvelopeWorkflow implements CommandInterface
{
    /**
     * The envelope recipients repository.
     */
    private Model $workflowStepsRepository;

    /**
     * Create instance of the command.
     */
    public function __construct(Model $workflowStepsRepository)
    {
        $this->workflowStepsRepository = $workflowStepsRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @throws RemoveWorkflowException if failed to remove the envelope workflow steps
     */
    public function __invoke(RemoveEnvelopeComponentsMessage $message)
    {
        //region Write Recipients
        try {
            $isDeleted = (bool) $this->workflowStepsRepository->deleteAllBy([
                'conditions' => [
                    'envelope' => $message->getEnvelopeId(),
                ],
            ]);
        } catch (Exception $e) {
            // Pass - handle below.
        }

        if (!$isDeleted) {
            throw new RemoveWorkflowException('Failed to write the workflow steps into the database', 0, $e ?? null);
        }
        //endregion Write Recipients
    }
}
