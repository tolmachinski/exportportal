<?php

namespace App\Documents\User;

abstract class AbstractUser implements UserInterface
{
    use UserTrait;

    /**
     * The user's type.
     *
     * @var string
     */
    private $type;

    /**
     * Creates user instance.
     */
    public function __construct(string $type, ?int $id = null, ?string $name = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
    }
}
