<?php

declare(strict_types=1);

namespace App\Envelope;

final class RecipientTypes
{
    public const SIGNER = 'signer';
    public const VIEWER = 'viewer';
    public const OPERATOR = 'operator';

    /**
     * @internal Prevents direct instantaniation
     */
    private function __construct()
    {
        // None shall pass!
    }
}
