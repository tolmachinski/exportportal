<?php

declare(strict_types=1);

namespace App\Messenger\Message\Command\Media;

/**
 * Command that copy image to storage
 */
final class CopyFileToStorage
{
    /**
     * The path to the file.
     */
    private string $filePath;

    /**
     * The destination directory.
     */
    private string $destination;

    /**
     * The name of the storage.
     */
    private string $storage;

    public function __construct(string $filePath, string $destination, string $storage)
    {
        $this->filePath = $filePath;
        $this->destination = $destination;
        $this->storage = $storage;
    }

    /**
     * Get the path to the file.
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * Set the path to the file.
     */
    public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * Get the destination directory.
     */
    public function getDestination(): string
    {
        return $this->destination;
    }

    /**
     * Set the destination directory.
     */
    public function setDestination(string $destination): self
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * Get the original name of the file.
     */
    public function getStorage(): string
    {
        return $this->storage;
    }

    /**
     * Set the original name of the file.
     */
    public function setStorage(string $storage): self
    {
        $this->storage = $storage;

        return $this;
    }
}
