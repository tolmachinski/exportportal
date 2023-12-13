<?php

namespace App\Documents\File;

use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;

interface FileInterface
{
    const DATE_FORMAT = 'Y-m-d\\TH:i:s.uP';

    /**
     * Checks if file has ID.
     */
    public function hasId(): bool;

    /**
     * Get the the ID of the file.
     */
    public function getId(): ?UuidInterface;

    /**
     * Returns an instance with the specified ID of the file.
     *
     * @return static
     */
    public function withId(UuidInterface $id);

    /**
     * Return an instance without file ID.
     *
     * @return static
     */
    public function withoutId();

    /**
     * Checks if file has name.
     */
    public function hasName(): bool;

    /**
     * Returns the name of the file.
     */
    public function getName(): ?string;

    /**
     * Returns an instance with the specified name of the file.
     *
     * @return static
     */
    public function withName(string $name);

    /**
     * Return an instance without name of the file.
     *
     * @return static
     */
    public function withoutName();

    /**
     * Checks if file has size.
     */
    public function hasSize(): bool;

    /**
     * Returns the file size of the file.
     */
    public function getSize(): ?int;

    /**
     * Returns an instance with the specified size of the file.
     *
     * @return static
     */
    public function withSize(int $size);

    /**
     * Return an instance without size of the file.
     *
     * @return static
     */
    public function withoutSize();

    /**
     * Checks if file has mime type.
     */
    public function hasMime(): bool;

    /**
     * Returns the file mime of the file.
     */
    public function getMime(): ?string;

    /**
     * Returns an instance with the specified mime type of the file.
     *
     * @return static
     */
    public function withMime(string $mimeType);

    /**
     * Return an instance without mime type of the file.
     *
     * @return static
     */
    public function withoutMime();

    /**
     * Checks if file has media.
     */
    public function hasMedia(): bool;

    /**
     * Returns the file media of the file.
     */
    public function getMedia(): ?string;

    /**
     * Returns an instance with the specified media of the file.
     *
     * @return static
     */
    public function withMedia(string $media);

    /**
     * Return an instance without media of the file.
     *
     * @return static
     */
    public function withoutMedia();

    /**
     * Checks if file has extension.
     */
    public function hasExtension(): bool;

    /**
     * Returns the file extension of the file.
     */
    public function getExtension(): ?string;

    /**
     * Returns an instance with the specified extension of the file.
     *
     * @return static
     */
    public function withExtension(string $extension);

    /**
     * Return an instance without extension of the file.
     *
     * @return static
     */
    public function withoutExtension();

    /**
     * Checks if file has original name.
     */
    public function hasOriginalName(): bool;

    /**
     * Returns the file original name.
     */
    public function getOriginalName(): ?string;

    /**
     * Returns an instance with the specified original name of the file.
     *
     * @return static
     */
    public function withOriginalName(string $originalName);

    /**
     * Return an instance without original name of the file.
     *
     * @return static
     */
    public function withoutOriginalName();

    /**
     * Checks if file has upload date.
     */
    public function hasUploadDate(): bool;

    /**
     * Get the date when the file was uploaded.
     */
    public function getUploadDate(): ?DateTimeImmutable;

    /**
     * Returns an instance with the specified upload date of the file.
     *
     * @return static
     */
    public function withUploadDate(DateTimeImmutable $uploadDate);

    /**
     * Return an instance without upload date of the file.
     *
     * @return static
     */
    public function withoutUploadDate();
}
