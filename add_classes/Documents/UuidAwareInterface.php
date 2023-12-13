<?php

namespace App\Documents;

use Ramsey\Uuid\UuidInterface;

interface UuidAwareInterface
{
    /**
     * Checks if UUID exists.
     */
    public function hasUuid(): bool;

    /**
     * Returns the UUID .
     */
    public function getUuid(): ?UuidInterface;

    /**
     * Returns an instance with the specified UUID.
     *
     * @return static
     */
    public function withUuid(UuidInterface $uuid);

    /**
     * Return an instance without UUID.
     *
     * @return static
     */
    public function withoutUuid();
}
