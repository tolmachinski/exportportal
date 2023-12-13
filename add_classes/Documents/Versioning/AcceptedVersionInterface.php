<?php

namespace App\Documents\Versioning;

use DateTimeImmutable;

interface AcceptedVersionInterface
{
    /**
     * Checks if version acceptance date exists.
     */
    public function hasAcceptanceDate(): bool;

    /**
     * Returns the date when version was accepted.
     */
    public function getAcceptanceDate(): ?DateTimeImmutable;

    /**
     * Returns an instance with the specified date of acceptance.
     *
     * @return static
     */
    public function withAcceptanceDate(DateTimeImmutable $date);

    /**
     * Return an instance without date of acceptance.
     *
     * @return static
     */
    public function withoutAcceptanceDate();
}
