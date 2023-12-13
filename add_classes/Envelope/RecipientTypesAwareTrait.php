<?php

declare(strict_types=1);

namespace App\Envelope;

use DomainException;

trait RecipientTypesAwareTrait
{
    /**
     * The list of allowed recipient types.
     */
    private array $allowedRecipientTypes = [
        RecipientTypes::SIGNER,
        RecipientTypes::VIEWER,
    ];

    /**
     * Asserts if provided recipient type is valid.
     *
     * @throws DomainException if type is not valid
     */
    private function assertValidRecipientType(string $type): void
    {
        if (!\in_array($type, $this->allowedRecipientTypes)) {
            throw new DomainException("The provided recipient type \"{$type}\" is not supported.");
        }
    }
}
