<?php

declare(strict_types=1);

namespace App\Messenger\Message\Command\EPDocs;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Command that deletes the file from EPDocs.
 *
 * @author Anton Zencenco
 */
final class DeleteFile
{
    /**
     * The file UUID.
     */
    private UuidInterface $fileId;

    /**
     * The user ID.
     */
    private ?int $userId;

    /**
     * @param string|UuidInterface $fileId the file UUID
     * @param null|int             $userId the user ID
     */
    public function __construct($fileId, ?int $userId = null)
    {
        $this->fileId = $fileId instanceof UuidInterface ? $fileId : Uuid::fromString($fileId);
        $this->userId = $userId;
    }

    /**
     * Get the file UUID.
     */
    public function getFileId(): UuidInterface
    {
        return $this->fileId;
    }

    /**
     * Set the file UUID.
     */
    public function setFileId(UuidInterface $fileId): self
    {
        $this->fileId = $fileId;

        return $this;
    }

    /**
     * Get the user ID.
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * Set the user ID.
     */
    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }
}
