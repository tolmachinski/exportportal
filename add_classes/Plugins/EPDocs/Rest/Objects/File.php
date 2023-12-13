<?php

namespace App\Plugins\EPDocs\Rest\Objects;

use App\Plugins\EPDocs\Rest\RestObject;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use RuntimeException;

class File extends RestObject
{
    /**
     * The temporary file ID.
     *
     * @var \Ramsey\Uuid\UuidInterface
     */
    private $id;

    /**
     * The file name.
     *
     * @var string
     */
    private $name;

    /**
     * The file original name.
     *
     * @var string
     */
    private $originalName;

    /**
     * The file extension.
     *
     * @var string
     */
    private $extension;

    /**
     * The file mime type.
     *
     * @var string
     */
    private $type;

    /**
     * The file size.
     *
     * @var int
     */
    private $size;

    /**
     * The date of file creation.
     *
     * @var DateTimeImmutable
     */
    private $createdAt;

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

    /**
     * Get the file name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the file name.
     *
     * @param string $name the file name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the file original name.
     *
     * @return string
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }

    /**
     * Set the file original name.
     *
     * @param string $originalName The file original name
     *
     * @return self
     */
    public function setOriginalName($originalName)
    {
        $this->originalName = $originalName;

        return $this;
    }

    /**
     * Get the file extension.
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Set the file extension.
     *
     * @param string $extension The file extension
     *
     * @return self
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Get the file mime type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the file mime type.
     *
     * @param string $type The file mime type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the file size.
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set the file size.
     *
     * @param int $size The file size
     *
     * @return self
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get the date of file creation.
     */
    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Set the date of file creation.
     *
     * @param null|DateTimeImmutable|string $createdAt
     *
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        if (is_string($createdAt)) {
            $createdAt = new DateTimeImmutable($createdAt);
            if (!$createdAt) {
                throw new RuntimeException("The 'createdAt' attribute has invalid format.");
            }
        }
        $this->createdAt = $createdAt;

        return $this;
    }
}
