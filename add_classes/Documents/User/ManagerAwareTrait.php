<?php

namespace App\Documents\User;

trait ManagerAwareTrait
{
    /**
     * The manager.
     *
     * @var null|UserInterface
     */
    private $manager;

    /**
     * Checks if user ID exists.
     */
    public function hasManager(): bool
    {
        return null !== $this->manager;
    }

    /**
     * Returns the ID of the user.
     */
    public function getManager(): ?UserInterface
    {
        return $this->manager;
    }

    /**
     * Returns an instance with the specified manager.
     *
     * @return static
     */
    public function withManager(UserInterface $manager)
    {
        $new = clone $this;
        $new->manager = $manager;

        return $new;
    }

    /**
     * Return an instance without ID.
     *
     * @return static
     */
    public function withoutManager()
    {
        $new = clone $this;
        $new->manager = null;

        return $new;
    }
}
