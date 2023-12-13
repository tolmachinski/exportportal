<?php

declare(strict_types=1);

namespace App\Envelope;

use DomainException;

trait WorkflowStepTypesAwareTrait
{
    /**
     * The list of allowed workflow step types.
     */
    private array $allowedWorkflowStepTypes = [
        WorkflowStepTypes::RECIPIENT_ROUTING,
        WorkflowStepTypes::PARALLEL_RECIPIENT_ROUTING,
    ];

    /**
     * Asserts if provided workflow step type is valid.
     *
     * @throws DomainException if type is not valid
     */
    private function assertValidWorkflowStepType(string $type): void
    {
        if (!\in_array($type, $this->allowedWorkflowStepTypes)) {
            throw new DomainException("The provided workflow step type \"{$type}\" is not supported.");
        }
    }
}
