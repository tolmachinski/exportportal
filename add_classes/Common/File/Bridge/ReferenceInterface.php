<?php

namespace App\Common\File\Bridge;

use Ramsey\Uuid\UuidInterface;

interface ReferenceInterface
{
    /**
     * Determines if reference is temporary.
     */
    public function isTemporary(): bool;

    /**
     * Checks if reference has UUID.
     */
    public function hasUuid(): bool;

    /**
     * Get the the UUID of the reference.
     */
    public function getUuid(): ?UuidInterface;

    /**
     * Returns an instance with the specified UUID of the reference.
     *
     * @return static
     */
    public function withUuid(?UuidInterface $uuid): ReferenceInterface;

    /**
     * Return an instance without reference UUID.
     *
     * @return static
     */
    public function withoutUuid(): ReferenceInterface;

    /**
     * Checks if reference has path.
     */
    public function hasPath(): bool;

    /**
     * Returns the path of the reference.
     */
    public function getPath(): ?string;

    /**
     * Returns an instance with the specified path of the reference.
     *
     * @return static
     */
    public function withPath(?string $path): ReferenceInterface;

    /**
     * Return an instance without path of the reference.
     *
     * @return static
     */
    public function withoutPath(): ReferenceInterface;

    /**
     * Checks if reference has preview path.
     */
    public function hasPreviewPath(): bool;

    /**
     * Returns the preview path of the reference.
     */
    public function getPreviewPath(): ?string;

    /**
     * Returns an instance with the specified preview path of the reference.
     *
     * @return static
     */
    public function withPreviewPath(?string $previewPath): ReferenceInterface;

    /**
     * Return an instance without preview path of the reference.
     *
     * @return static
     */
    public function withoutPreviewPath(): ReferenceInterface;

    /**
     * Checks if file has TTL (time To Live).
     */
    public function hasTtl(): bool;

    /**
     * Get the the TTL (time To Live) of the file.
     */
    public function getTtl(): ?int;

    /**
     * Returns an instance with the specified TTL (time To Live) of the file.
     *
     * @return static
     */
    public function withTtl(?int $ttl): ReferenceInterface;

    /**
     * Return an instance without file TTL (time To Live).
     *
     * @return static
     */
    public function withoutTtl(): ReferenceInterface;
}
