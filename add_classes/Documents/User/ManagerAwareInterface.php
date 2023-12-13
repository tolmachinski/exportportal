<?php

namespace App\Documents\User;

interface ManagerAwareInterface
{
    /**
     * Checks if user ID exists.
     */
    public function hasManager(): bool;

    /**
     * Returns the ID of the user.
     */
    public function getManager(): ?UserInterface;

    /**
     * Returns an instance with the specified manager.
     *
     * @return static
     */
    public function withManager(UserInterface $manager);

    /**
     * Return an instance without ID.
     *
     * @return static
     */
    public function withoutManager();
}
