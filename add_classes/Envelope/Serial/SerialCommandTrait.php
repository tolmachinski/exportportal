<?php

declare(strict_types=1);

namespace App\Envelope\Serial;

use App\Common\Exceptions\NotFoundException;
use App\Envelope\Serial\Exception\NotSerialCommandException;
use App\Envelope\WorkflowStepTypes;
use Doctrine\Common\Collections\Collection;

trait SerialCommandTrait
{
    /**
     * Asserts if the this serial command is appliable to the envelope.
     *
     * @throws NotSerialCommandException if envelope routing is not
     */
    protected function assertCurrentRoutingIsSerial(Collection $currentRouting, ?array $currentWorkflowStep): void
    {
        if (
            $currentRouting->count() > 1
            || (
                null !== $currentWorkflowStep
                && WorkflowStepTypes::RECIPIENT_ROUTING !== $currentWorkflowStep['action']
            )
        ) {
            throw new NotSerialCommandException('The command is appliable only to the serial routing.');
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
                    'extended_sender as sender',
                    'order_reference',
                ],
            ])
        ) {
            throw new NotFoundException(sprintf('The envelope with ID %s is not found', varToString($envelopeId)));
        }

        return $envelope;
    }
}
