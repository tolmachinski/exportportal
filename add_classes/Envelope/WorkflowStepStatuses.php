<?php

declare(strict_types=1);

namespace App\Envelope;

final class WorkflowStepStatuses
{
    public const INACTIVE = 'inactive';

    public const PAUSED = 'paused';

    public const PENDING = 'pending';

    public const COMPLETED = 'completed';

    public const IN_PROGRESS = 'in_progress';

    /**
     * @internal Prevents direct instantaniation
     */
    private function __construct()
    {
        // None shall pass!
    }
}
