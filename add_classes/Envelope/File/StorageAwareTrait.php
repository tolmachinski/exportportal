<?php

declare(strict_types=1);

namespace App\Envelope\File;

trait StorageAwareTrait
{
    /**
     * The storage for the document files.
     */
    protected ?StorageInterface $fileStorage;

    /**
     * Get the storage for the document files.
     */
    public function getFileStorage(): ?StorageInterface
    {
        return $this->fileStorage;
    }

    /**
     * Set the storage for the document files.
     *
     * @return self
     */
    public function setFileStorage(?StorageInterface $fileStorage)
    {
        $this->fileStorage = $fileStorage;

        return $this;
    }
}
