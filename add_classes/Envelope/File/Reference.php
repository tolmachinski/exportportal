<?php

declare(strict_types=1);

namespace App\Envelope\File;

use Ramsey\Uuid\UuidInterface;

class Reference implements ReferenceInterface
{
    /**
     * The UUID of the reference.
     */
    private ?UuidInterface $uuid;

    /**
     * The file reference path.
     */
    private ?string $path;

    /**
     * The file reference preview path (if exists).
     */
    private ?string $previewPath;

    /**
     * The TTL (time To Live) of the reference.
     */
    private int $ttl = 0;

    public function __construct(
        ?UuidInterface $uuid = null,
        ?string $path = null,
        ?string $previewPath = null,
        ?int $ttl = 0
    ) {
        $this->ttl = $ttl ?? 0;
        $this->uuid = $uuid;
        $this->path = $path;
        $this->temporary = $temporary ?? false;
        $this->previewPath = $previewPath;
    }

    /**
     * {@inheritdoc}
     */
    public function isTemporary(): bool
    {
        return null !== $this->ttl && $this->ttl >= 0;
    }

    /**
     * {@inheritdoc}
     */
    public function hasUuid(): bool
    {
        return null !== $this->uuid;
    }

    /**
     * {@inheritdoc}
     */
    public function getUuid(): ?UuidInterface
    {
        return $this->uuid;
    }

    /**
     * {@inheritdoc}
     */
    public function withUuid(?UuidInterface $uuid): ReferenceInterface
    {
        $new = clone $this;
        $new->uuid = $uuid;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutUuid(): ReferenceInterface
    {
        $new = clone $this;
        $new->uuid = null;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function hasPath(): bool
    {
        return null !== $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function withPath(?string $path): ReferenceInterface
    {
        $new = clone $this;
        $new->path = $path ?: null;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutPath(): ReferenceInterface
    {
        $new = clone $this;
        $new->path = null;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function hasPreviewPath(): bool
    {
        return null !== $this->previewPath;
    }

    /**
     * {@inheritdoc}
     */
    public function getPreviewPath(): ?string
    {
        return $this->previewPath;
    }

    /**
     * {@inheritdoc}
     */
    public function withPreviewPath(?string $previewPath): ReferenceInterface
    {
        $new = clone $this;
        $new->previewPath = $previewPath ?: null;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutPreviewPath(): ReferenceInterface
    {
        $new = clone $this;
        $new->previewPath = null;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function hasTtl(): bool
    {
        return null !== $this->ttl;
    }

    /**
     * {@inheritdoc}
     */
    public function getTtl(): ?int
    {
        return $this->ttl;
    }

    /**
     * {@inheritdoc}
     */
    public function withTtl(?int $ttl): ReferenceInterface
    {
        $new = clone $this;
        $new->ttl = $ttl ?? null;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutTtl(): ReferenceInterface
    {
        $new = clone $this;
        $new->ttl = null;

        return $new;
    }
}
