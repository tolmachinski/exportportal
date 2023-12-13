<?php

namespace App\Documents\User;

use App\Documents\UuidAwareInterface;
use App\Documents\UuidAwareTrait;
use Ramsey\Uuid\UuidInterface;

class Manager extends AbstractUser implements UuidAwareInterface
{
    use UuidAwareTrait;

    /**
     * Creates user instance.
     *
     * @param int    $id
     * @param string $name
     */
    public function __construct(?int $id = null, ?string $name = null, ?UuidInterface $uuid = null)
    {
        parent::__construct(UserTypesInterface::MANAGER, $id, $name);

        $this->uuid = $uuid;
    }
}
