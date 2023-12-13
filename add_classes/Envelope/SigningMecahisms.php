<?php

declare(strict_types=1);

namespace App\Envelope;

final class SigningMecahisms
{
    public const NATIVE = 'native';
    public const DOCUSIGN = 'docusign';

    /**
     * @internal Prevents direct instantaniation
     */
    private function __construct()
    {
        // None shall pass!
    }
}
