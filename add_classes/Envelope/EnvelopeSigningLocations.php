<?php

declare(strict_types=1);

namespace App\Envelope;

final class EnvelopeSigningLocations
{
    public const ONLINE = 'online';
    public const IN_PERSON = 'in_person';

    /**
     * @internal Prevents direct instantaniation
     */
    private function __construct()
    {
        // None shall pass!
    }
}
