<?php

namespace App\Documents;

use Ramsey\Uuid\UuidInterface;

trait UuidAwareTrait
{
    /**
     * The user's UUID.
     *
     * @var null|UuidInterface
     */
    private $uuid;

    /**
     * Checks if user UUID exists.
     */
    public function hasUuid(): bool
    {
        return null !== $this->uuid;
    }

    /**
     * Returns the UUID of the user.
     */
    public function getUuid(): ?UuidInterface
    {
        return $this->uuid;
    }

    /**
     * Returns an instance with the specified UUID.
     *
     * @return static
     */
    public function withUuid(UuidInterface $uuid)
    {
        $new = clone $this;
        $new->uuid = $uuid;

        return $new;
    }

    /**
     * Return an instance without UUID.
     *
     * @return static
     */
    public function withoutUuid()
    {
        $new = clone $this;
        $new->uuid = null;

        return $new;
    }
}
