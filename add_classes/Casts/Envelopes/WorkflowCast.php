<?php

declare(strict_types=1);

namespace App\Casts\Envelopes;

use App\Common\Database\AttributeCastInterface;
use App\Common\Database\Model;
use BadMethodCallException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class WorkflowCast implements AttributeCastInterface
{
    /**
     * {@inheritdoc}
     *
     * @param null|Collection $workflowSteps
     */
    public function get(AbstractPlatform $platform, Model $model, string $key, $workflowSteps, array $attributes = [])
    {
        if (null === $workflowSteps) {
            $workflowSteps = new ArrayCollection();
        }
        $workflowSteps = new ArrayCollection(\arrayByKey($workflowSteps->toArray(), 'uuid'));
        $currentWorkflowStepUuid = $attributes['current_workflow_step'] ?? null;
        $currentWorkflowStep = null !== $currentWorkflowStepUuid ? ($workflowSteps[(string) $currentWorkflowStepUuid] ?? null) : null;

        return [
            'current_step_uuid' => $currentWorkflowStepUuid,
            'current_step_id'   => $currentWorkflowStep['id'] ?? null,
            'current_step'      => $currentWorkflowStep,
            'steps'             => $workflowSteps,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function set(AbstractPlatform $platform, Model $model, string $key, $value, array $attributes = [])
    {
        throw new BadMethodCallException('This cast class is read-only.');
    }
}
