<?php

declare(strict_types=1);

namespace App\Messenger\Message\Command\DropList;

/**
 * Replace image for Droplist item
 */
final class ReplaceImage
{
    /**
     * The droplist entry ID.
     */
    private int $id;

    /**
     * The path to the source image.
     */
    private string $sourceImagePath;

    /**
     * The storage where source image is placed.
     */
    private string $sourceStorage;

    public function __construct(int $id, string $sourceImagePath, string $sourceStorage)
    {
        $this->id = $id;
        $this->sourceImagePath = $sourceImagePath;
        $this->sourceStorage = $sourceStorage;
    }

    /**
     * Get the droplist entry ID.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set the droplist entry ID.
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the path to the source image.
     */
    public function getSourceImagePath(): string
    {
        return $this->sourceImagePath;
    }

    /**
     * Set the path to the source image.
     */
    public function setSourceImagePath(string $sourceImagePath): self
    {
        $this->sourceImagePath = $sourceImagePath;

        return $this;
    }

    /**
     * Get the storage where source image is placed.
     */
    public function getSourceStorage(): string
    {
        return $this->sourceStorage;
    }

    /**
     * Set the storage where source image is placed.
     */
    public function setSourceStorage(string $sourceStorage): self
    {
        $this->sourceStorage = $sourceStorage;

        return $this;
    }
}
