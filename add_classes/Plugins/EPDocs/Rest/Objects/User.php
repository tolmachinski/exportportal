<?php

namespace App\Plugins\EPDocs\Rest\Objects;

use App\Plugins\EPDocs\Rest\RestObject;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @method static self fromArray(array $data) Fills user object from array.
 */
class User extends RestObject
{
    /**
     * The temporary file ID.
     *
     * @var \Ramsey\Uuid\UuidInterface
     */
    private $id;

    /**
     * Get the temporary file ID.
     *
     * @return \Ramsey\Uuid\UuidInterface
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the temporary file ID.
     *
     * @param \Ramsey\Uuid\UuidInterface|string $id The temporary file ID
     *
     * @return self
     */
    public function setId($id)
    {
        if (!$id instanceof UuidInterface) {
            $id = is_string($id) ? Uuid::fromString($id) : (is_int($id) ? Uuid::fromInteger($id) : Uuid::fromBytes($id));
        }

        $this->id = $id;

        return $this;
    }
}
