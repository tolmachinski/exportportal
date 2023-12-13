<?php

namespace App\Documents\User;

interface UserInterface
{
    /**
     * Checks if user ID exists.
     */
    public function hasId(): bool;

    /**
     * Returns the ID of the user.
     */
    public function getId(): ?int;

    /**
     * Returns an instance with the specified ID.
     *
     * @return static
     */
    public function withId(int $id);

    /**
     * Return an instance without ID.
     *
     * @return static
     */
    public function withoutId();

    /**
     * Checks if user name exists.
     */
    public function hasName(): bool;

    /**
     * Returns the name of the user.
     */
    public function getName(): ?string;

    /**
     * Returns an instance with the specified name.
     *
     * @return static
     */
    public function withName(string $name);

    /**
     * Return an instance without name.
     *
     * @return static
     */
    public function withoutName();
}
