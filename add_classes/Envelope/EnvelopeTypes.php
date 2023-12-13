<?php

declare(strict_types=1);

namespace App\Envelope;

final class EnvelopeTypes
{
    public const PERSONAL = 'personal';
    public const INTERNAL = 'internal';

    /**
     * @internal Prevents direct instantaniation
     */
    private function __construct()
    {
        // None shall pass!
    }
}
