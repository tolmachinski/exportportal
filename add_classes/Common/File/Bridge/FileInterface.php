<?php

namespace App\Common\File\Bridge;

use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;

interface FileInterface
{
    /**
     * Checks if file has ID.
     */
    public function hasId(): bool;

    /**
     * Get the the ID of the file.
     */
    public function getId(): ?int;

    /**
     * Returns an instance with the specified ID of the file.
     *
     * @return static
     */
    public function withId(?int $id): FileInterface;

    /**
     * Return an instance without file ID.
     *
     * @return static
     */
    public function withoutId(): FileInterface;

    /**
     * Checks if file has UUID.
     */
    public function hasUuid(): bool;

    /**
     * Get the the UUID of the file.
     */
    public function getUuid(): ?UuidInterface;

    /**
     * Returns an instance with the specified UUID of the file.
     *
     * @return static
     */
    public function withUuid(?UuidInterface $id): FileInterface;

    /**
     * Return an instance without file UUID.
     *
     * @return static
     */
    public function withoutUuid(): FileInterface;

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
    public function withName(?string $name): FileInterface;

    /**
     * Return an instance without name of the file.
     *
     * @return static
     */
    public function withoutName(): FileInterface;

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
    public function withOriginalName(?string $originalName): FileInterface;

    /**
     * Return an instance without original name of the file.
     *
     * @return static
     */
    public function withoutOriginalName(): FileInterface;

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
    public function withExtension(?string $extension): FileInterface;

    /**
     * Return an instance without extension of the file.
     *
     * @return static
     */
    public function withoutExtension(): FileInterface;

    /**
     * Checks if file has path.
     */
    public function hasPath(): bool;

    /**
     * Returns the path of the file.
     */
    public function getPath(): ?string;

    /**
     * Returns an instance with the specified path of the file.
     *
     * @return static
     */
    public function withPath(?string $path): FileInterface;

    /**
     * Return an instance without path of the file.
     *
     * @return static
     */
    public function withoutPath(): FileInterface;

    /**
     * Checks if file has URL.
     */
    public function hasUrl(): bool;

    /**
     * Returns the URL of the file.
     */
    public function getUrl(): ?string;

    /**
     * Returns an instance with the specified URL of the file.
     *
     * @return static
     */
    public function withUrl(?string $url): FileInterface;

    /**
     * Return an instance without URL of the file.
     *
     * @return static
     */
    public function withoutUrl(): FileInterface;

    /**
     * Checks if file has mime type.
     */
    public function hasType(): bool;

    /**
     * Returns the mime type of the file.
     */
    public function getType(): ?string;

    /**
     * Returns an instance with the specified mime type of the file.
     *
     * @return static
     */
    public function withType(string $type): FileInterface;

    /**
     * Return an instance without mime type of the file.
     *
     * @return static
     */
    public function withoutType(): FileInterface;

    /**
     * Checks if file has size.
     */
    public function hasSize(): bool;

    /**
     * Returns the size of the file.
     */
    public function getSize(): ?int;

    /**
     * Returns an instance with the specified size of the file.
     *
     * @return static
     */
    public function withSize(?int $size): FileInterface;

    /**
     * Return an instance without size of the file.
     *
     * @return static
     */
    public function withoutSize(): FileInterface;

    /**
     * Checks if file has creation date.
     */
    public function hasCreatedAt(): bool;

    /**
     * Returns the date when the file was created.
     */
    public function getCreatedAt(): ?DateTimeImmutable;

    /**
     * Returns an instance with the specified creation date of the file.
     *
     * @return static
     */
    public function withCreatedAt(?DateTimeImmutable $createdAt): FileInterface;

    /**
     * Return an instance without creation date of the file.
     *
     * @return static
     */
    public function withoutCreatedAt(): FileInterface;
}
