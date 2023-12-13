<?php

namespace App\Documents\Versioning;

use DateTimeImmutable;

trait AcceptedVersionTrait
{
    /**
     * The date when version was accepted.
     *
     * @var null|DateTimeImmutable
     */
    private $acceptanceDate;

    /**
     * Checks if version acceptance date exists.
     */
    public function hasAcceptanceDate(): bool
    {
        return null !== $this->acceptanceDate;
    }

    /**
     * Returns the date when version was accepted.
     */
    public function getAcceptanceDate(): ?DateTimeImmutable
    {
        return $this->acceptanceDate;
    }

    /**
     * Returns an instance with the specified date of acceptance.
     *
     * @return static
     */
    public function withAcceptanceDate(DateTimeImmutable $date)
    {
        $new = clone $this;
        $new->acceptanceDate = $date;

        return $new;
    }

    /**
     * Return an instance without date of acceptance.
     *
     * @return static
     */
    public function withoutAcceptanceDate()
    {
        $new = clone $this;
        $new->acceptanceDate = null;

        return $new;
    }
}
