<?php

namespace App\Documents\Versioning;

use DateTimeImmutable;

trait ExpiringVersionTrait
{
    /**
     * The version expiration date.
     *
     * @var \DateTimeImmutable
     */
    private $expirationDate;

    /**
     * Checks if version expired.
     */
    public function isExpired(): bool
    {
        if (!$this->hasExpirationDate()) {
            return false;
        }
        $now = new DateTimeImmutable();

        return $this->expirationDate->modify('midnight') <= $now->modify('midnight');
    }

    /**
     * Checks if version expiration date exists.
     */
    public function hasExpirationDate(): bool
    {
        return null !== $this->expirationDate;
    }

    /**
     * Returns the date when version will expire.
     */
    public function getExpirationDate(): ?DateTimeImmutable
    {
        return $this->expirationDate;
    }

    /**
     * Returns an instance with the specified expiration date.
     *
     * @return static
     */
    public function withExpirationDate(DateTimeImmutable $date)
    {
        $new = clone $this;
        $new->expirationDate = $date;

        return $new;
    }

    /**
     * Return an instance without expiration date.
     *
     * @return static
     */
    public function withoutExpirationDate()
    {
        $new = clone $this;
        $new->expirationDate = null;

        return $new;
    }
}
