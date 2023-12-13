<?php

declare(strict_types=1);

namespace App\Envelope;

final class EnvelopeWorkflowStatuses
{
    public const PAUSED = 'paused';

    public const INACTIVE = 'inactive';

    public const IN_PROGRESS = 'in_progress';

    public const COMPLETED = 'completed';

    /**
     * @internal Prevents direct instantaniation
     */
    private function __construct()
    {
        // None shall pass!
    }
}
