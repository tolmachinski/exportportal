<?php

namespace App\Documents\File;

interface FileAwareInterface
{
    /**
     * Checks if file exists.
     */
    public function hasFile(): bool;

    /**
     * Returns the file instance if it exists.
     */
    public function getFile(): ?FileInterface;

    /**
     * Returns an instance with the specified file.
     *
     * @return static
     */
    public function withFile(FileInterface $file);

    /**
     * Return an instance without file.
     *
     * @return static
     */
    public function withoutFile();
}
