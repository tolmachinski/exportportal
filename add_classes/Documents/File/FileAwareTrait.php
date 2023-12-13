<?php

namespace App\Documents\File;

trait FileAwareTrait
{
    /**
     * The file instance.
     *
     * @var null|FileInterface
     */
    private $file;

    /**
     * Checks if file exists.
     */
    public function hasFile(): bool
    {
        return null !== $this->file;
    }

    /**
     * Returns the file instance if it exists.
     */
    public function getFile(): ?FileInterface
    {
        return $this->file;
    }

    /**
     * Returns an instance with the specified file.
     *
     * @return static
     */
    public function withFile(FileInterface $file)
    {
        $new = clone $this;
        $new->file = $file;

        return $new;
    }

    /**
     * Return an instance without file.
     *
     * @return static
     */
    public function withoutFile()
    {
        $new = clone $this;
        $new->file = null;

        return $new;
    }
}
