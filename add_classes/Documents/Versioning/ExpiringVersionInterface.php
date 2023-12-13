<?php

namespace App\Documents\Versioning;

use DateTimeImmutable;

interface ExpiringVersionInterface
{
    /**
     * Checks if version expired.
     */
    public function isExpired(): bool;

    /**
     * Checks if version expiration date exists.
     */
    public function hasExpirationDate(): bool;

    /**
     * Returns the date when version will expire.
     */
    public function getExpirationDate(): ?DateTimeImmutable;

    /**
     * Returns an instance with the specified expiration date.
     *
     * @return static
     */
    public function withExpirationDate(DateTimeImmutable $date);

    /**
     * Return an instance without expiration date.
     *
     * @return static
     */
    public function withoutExpirationDate();
}
