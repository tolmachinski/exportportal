<?php

namespace App\Plugins\EPDocs\Rest\Objects;

use App\Plugins\EPDocs\Rest\RestObject;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class AccessToken extends RestObject
{
    /**
     * The access token ID.
     *
     * @var \Ramsey\Uuid\UuidInterface
     */
    private $id;

    /**
     * The access token ttl.
     *
     * @var int
     */
    private $ttl;

    /**
     * The relative URL path for this access token.
     *
     * @var string
     */
    private $path;

    /**
     * The relative URL path for this access token.
     *
     * @var string
     */
    private $previewPath;

    /**
     * The HMAC signed token hash.
     *
     * @var string
     */
    private $hash;

    /**
     * The flag indicating that access token is temporary.
     *
     * @var bool
     */
    private $isTemporary;

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
     * Get the access token ttl.
     *
     * @return int
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * Set the access token ttl.
     *
     * @param int $ttl the access token ttl
     *
     * @return self
     */
    public function setTtl($ttl)
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * Get the relative URL path for this access token.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the relative URL path for this access token.
     *
     * @param string $path The relative URL path for this access token
     *
     * @return self
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get the relative URL path for this access token.
     *
     * @return string
     */
    public function getPreviewPath()
    {
        return $this->previewPath;
    }

    /**
     * Set the relative URL path for this access token.
     *
     * @param string $path The relative URL path for this access token
     *
     * @return self
     */
    public function setPreviewPath($previewPath)
    {
        $this->previewPath = $previewPath;

        return $this;
    }

    /**
     * Get the HMAC signed token hash.
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set the HMAC signed token hash.
     *
     * @param string $hash The HMAC signed token hash
     *
     * @return self
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get the flag indicating that access token is temporary.
     *
     * @return bool
     */
    public function getIsTemporary()
    {
        return $this->isTemporary;
    }

    /**
     * Set the flag indicating that access token is temporary.
     *
     * @param bool $isTemporary the flag indicating that access token is temporary
     *
     * @return self
     */
    public function setIsTemporary($isTemporary)
    {
        $this->isTemporary = $isTemporary;

        return $this;
    }
}
