<?php

namespace App\Documents\User;

trait UserTrait
{
    /**
     * The user's ID.
     *
     * @var null|int
     */
    private $id;

    /**
     * The users's name.
     *
     * @var null|string
     */
    private $name;

    /**
     * Checks if user ID exists.
     */
    public function hasId(): bool
    {
        return null !== $this->id;
    }

    /**
     * Returns the ID of the user.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Returns an instance with the specified ID.
     *
     * @return static
     */
    public function withId(int $id)
    {
        $new = clone $this;
        $new->id = !empty($id) ? (int) $id : null;

        return $new;
    }

    /**
     * Return an instance without ID.
     *
     * @return static
     */
    public function withoutId()
    {
        $new = clone $this;
        $new->id = null;

        return $new;
    }

    /**
     * Checks if user name exists.
     */
    public function hasName(): bool
    {
        return null !== $this->name;
    }

    /**
     * Returns the name of the user.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Returns an instance with the specified name.
     *
     * @return static
     */
    public function withName(string $name)
    {
        $new = clone $this;
        $new->name = !empty($name) ? (string) $name : null;

        return $new;
    }

    /**
     * Return an instance without name.
     *
     * @return static
     */
    public function withoutName()
    {
        $new = clone $this;
        $new->name = null;

        return $new;
    }
}
