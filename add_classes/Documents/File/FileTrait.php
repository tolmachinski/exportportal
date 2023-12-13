<?php

namespace App\Documents\File;

use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;

trait FileTrait
{
    /**
     * The the ID of the file.
     *
     * @var UuidInterface
     */
    private $id;

    /**
     * The name of the file.
     *
     * @var null|string
     */
    private $name;

    /**
     * The size of the file.
     *
     * @var null|int
     */
    private $size;

    /**
     * The mime of the file.
     *
     * @var null|string
     */
    private $mime;

    /**
     * The media of the file.
     *
     * @var null|string
     */
    private $media;

    /**
     * The extension of the file.
     *
     * @var null|string
     */
    private $extension;

    /**
     * The original name of the file.
     *
     * @var null|string
     */
    private $originalName;

    /**
     * The date when the file was uploaded.
     *
     * @var null|DateTimeImmutable
     */
    private $uploadDate;

    /**
     * Checks if file has ID.
     */
    public function hasId(): bool
    {
        return null !== $this->id;
    }

    /**
     * Get the the ID of the file.
     */
    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    /**
     * Returns an instance with the specified ID of the file.
     *
     * @return static
     */
    public function withId(UuidInterface $id)
    {
        $new = clone $this;
        $new->id = $id;

        return $new;
    }

    /**
     * Return an instance without file ID.
     *
     * @return static
     */
    public function withoutId()
    {
        $new = clone $this;
        $new->id = null;

        return $new;
    }

    /**
     * Checks if file has name.
     */
    public function hasName(): bool
    {
        return null !== $this->name;
    }

    /**
     * Returns the name of the file.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Returns an instance with the specified name of the file.
     *
     * @return static
     */
    public function withName(string $name)
    {
        $new = clone $this;
        $new->name = !empty($name) ? $name : null;

        return $new;
    }

    /**
     * Return an instance without name of the file.
     *
     * @return static
     */
    public function withoutName()
    {
        $new = clone $this;
        $new->name = null;

        return $new;
    }

    /**
     * Checks if file has size.
     */
    public function hasSize(): bool
    {
        return null !== $this->size;
    }

    /**
     * Returns the file size of the file.
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * Returns an instance with the specified size of the file.
     *
     * @return static
     */
    public function withSize(int $size)
    {
        $new = clone $this;
        $new->size = (int) $size;

        return $new;
    }

    /**
     * Return an instance without size of the file.
     *
     * @return static
     */
    public function withoutSize()
    {
        $new = clone $this;
        $new->size = null;

        return $new;
    }

    /**
     * Checks if file has mime type.
     */
    public function hasMime(): bool
    {
        return null !== $this->mime;
    }

    /**
     * Returns the file mime of the file.
     */
    public function getMime(): ?string
    {
        return $this->mime;
    }

    /**
     * Returns an instance with the specified mime type of the file.
     *
     * @return static
     */
    public function withMime(string $mimeType)
    {
        $new = clone $this;
        $new->mime = !empty($mimeType) ? $mimeType : null;

        return $new;
    }

    /**
     * Return an instance without mime type of the file.
     *
     * @return static
     */
    public function withoutMime()
    {
        $new = clone $this;
        $new->mime = null;

        return $new;
    }

    /**
     * Checks if file has media.
     */
    public function hasMedia(): bool
    {
        return null !== $this->media;
    }

    /**
     * Returns the file media of the file.
     */
    public function getMedia(): ?string
    {
        return $this->media;
    }

    /**
     * Returns an instance with the specified media of the file.
     *
     * @return static
     */
    public function withMedia(string $media)
    {
        $new = clone $this;
        $new->media = !empty($media) ? $media : null;

        return $new;
    }

    /**
     * Return an instance without media of the file.
     *
     * @return static
     */
    public function withoutMedia()
    {
        $new = clone $this;
        $new->media = null;

        return $new;
    }

    /**
     * Checks if file has extension.
     */
    public function hasExtension(): bool
    {
        return null !== $this->extension;
    }

    /**
     * Returns the file extension of the file.
     */
    public function getExtension(): ?string
    {
        return $this->extension;
    }

    /**
     * Returns an instance with the specified extension of the file.
     *
     * @return static
     */
    public function withExtension(string $extension)
    {
        $new = clone $this;
        $new->extension = !empty($extension) ? $extension : null;

        return $new;
    }

    /**
     * Return an instance without extension of the file.
     *
     * @return static
     */
    public function withoutExtension()
    {
        $new = clone $this;
        $new->extension = null;

        return $new;
    }

    /**
     * Checks if file has original name.
     */
    public function hasOriginalName(): bool
    {
        return null !== $this->originalName;
    }

    /**
     * Returns the file original name.
     */
    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    /**
     * Returns an instance with the specified original name of the file.
     *
     * @return static
     */
    public function withOriginalName(string $originalName)
    {
        $new = clone $this;
        $new->originalName = !empty($originalName) ? $originalName : null;

        return $new;
    }

    /**
     * Return an instance without original name of the file.
     *
     * @return static
     */
    public function withoutOriginalName()
    {
        $new = clone $this;
        $new->originalName = null;

        return $new;
    }

    /**
     * Checks if file has upload date.
     */
    public function hasUploadDate(): bool
    {
        return null !== $this->uploadDate;
    }

    /**
     * Get the date when the file was uploaded.
     */
    public function getUploadDate(): ?DateTimeImmutable
    {
        return $this->uploadDate;
    }

    /**
     * Returns an instance with the specified upload date of the file.
     *
     * @return static
     */
    public function withUploadDate(DateTimeImmutable $uploadDate)
    {
        $new = clone $this;
        $new->uploadDate = $uploadDate;

        return $new;
    }

    /**
     * Return an instance without upload date of the file.
     *
     * @return static
     */
    public function withoutUploadDate()
    {
        $new = clone $this;
        $new->uploadDate = null;

        return $new;
    }
}
