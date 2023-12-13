<?php

declare(strict_types=1);

namespace App\Envelope;

final class WorkflowStepTypes
{
    public const RECIPIENT_ROUTING = 'recipient_routing';

    public const PARALLEL_RECIPIENT_ROUTING = 'parallel_recipient_routing';

    /**
     * @internal Prevents direct instantaniation
     */
    private function __construct()
    {
        // None shall pass!
    }
}
