<?php

declare(strict_types=1);

namespace App\Messenger\Message\Command\Profile;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Command that accepts the temporary file as profile edit request document file.
 *
 * @author Anton Zencenco
 */
final class AcceptTemporaryFile
{
    /**
     * The file UUID.
     */
    private UuidInterface $fileId;

    /**
     * The profile edit request document ID.
     */
    private int $documentId;

    /**
     * The user ID value.
     */
    private ?int $userId;

    /**
     * @param string|UuidInterface $fileId the file UUID
     */
    public function __construct($fileId, int $documentId, ?int $userId = null)
    {
        $this->userId = $userId;
        $this->fileId = $fileId instanceof UuidInterface ? $fileId : Uuid::fromString($fileId);
        $this->documentId = $documentId;
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
     * Get the profile edit request document ID.
     */
    public function getDocumentId(): int
    {
        return $this->documentId;
    }

    /**
     * Set the profile edit request document ID.
     */
    public function setDocumentId(int $documentId): self
    {
        $this->documentId = $documentId;

        return $this;
    }

    /**
     * Get the user ID value.
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * Set the user ID value.
     */
    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }
}
