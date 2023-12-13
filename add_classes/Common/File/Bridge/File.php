<?php

declare(strict_types=1);

namespace App\Common\File\Bridge;

use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;

class File implements FileInterface
{
    private ?int $id;

    /**
     * The the UUID of the file.
     */
    private ?UuidInterface $uuid;

    /**
     * The name of the file.
     */
    private ?string $name;

    /**
     * The original name of the file.
     */
    private ?string $originalName;

    /**
     * The extension of the file.
     */
    private ?string $extension;

    /**
     * The path to the file.
     */
    private ?string $path;

    /**
     * The URL to the file.
     */
    private ?string $url;

    /**
     * The file mime type.
     */
    private ?string $type;

    /**
     * The size of the file.
     */
    private ?int $size;

    /**
     * The date of file creation.
     */
    private ?DateTimeImmutable $createdAt;

    /**
     * Created the file instance.
     */
    public function __construct(
        ?int $id = null,
        ?UuidInterface $uuid = null,
        ?string $name = null,
        ?string $originalName = null,
        ?string $extension = null,
        ?string $path = null,
        ?string $url = null,
        ?string $type = null,
        ?int $size = 0,
        ?DateTimeImmutable $createdAt = null
    ) {
        $this->id = $id;
        $this->uuid = $uuid;
        $this->name = $name;
        $this->size = $size ?? 0;
        $this->type = $type;
        $this->path = $path;
        $this->url = $url;
        $this->extension = $extension;
        $this->originalName = $originalName;
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
    }

    /**
     * {@inheritdoc}
     */
    public function hasId(): bool
    {
        return null !== $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function withId(?int $id): FileInterface
    {
        $new = clone $this;
        $new->id = $id ?? null;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutId(): FileInterface
    {
        $new = clone $this;
        $new->id = null;

        return $new;
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
    public function withUuid(?UuidInterface $uuid): FileInterface
    {
        $new = clone $this;
        $new->uuid = $uuid;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutUuid(): FileInterface
    {
        $new = clone $this;
        $new->uuid = null;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function hasName(): bool
    {
        return null !== $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function withName(?string $name): FileInterface
    {
        $new = clone $this;
        $new->name = $name ?: null;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutName(): FileInterface
    {
        $new = clone $this;
        $new->name = null;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function hasOriginalName(): bool
    {
        return null !== $this->originalName;
    }

    /**
     * {@inheritdoc}
     */
    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    /**
     * {@inheritdoc}
     */
    public function withOriginalName(?string $originalName): FileInterface
    {
        $new = clone $this;
        $new->originalName = $originalName ?: null;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutOriginalName(): FileInterface
    {
        $new = clone $this;
        $new->originalName = null;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function hasExtension(): bool
    {
        return null !== $this->extension;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension(): ?string
    {
        return $this->extension;
    }

    /**
     * {@inheritdoc}
     */
    public function withExtension(?string $extension): FileInterface
    {
        $new = clone $this;
        $new->extension = $extension ?: null;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutExtension(): FileInterface
    {
        $new = clone $this;
        $new->extension = null;

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
    public function withPath(?string $path): FileInterface
    {
        $new = clone $this;
        $new->path = $path ?: null;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutPath(): FileInterface
    {
        $new = clone $this;
        $new->path = null;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function hasUrl(): bool
    {
        return null !== $this->url;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * {@inheritdoc}
     */
    public function withUrl(?string $url): FileInterface
    {
        $new = clone $this;
        $new->url = $url ?: null;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutUrl(): FileInterface
    {
        $new = clone $this;
        $new->url = null;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function hasType(): bool
    {
        return null !== $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): ?string
    {
        return $this->mime;
    }

    /**
     * {@inheritdoc}
     */
    public function withType(string $type): FileInterface
    {
        $new = clone $this;
        $new->type = $type ?: null;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutType(): FileInterface
    {
        $new = clone $this;
        $new->type = null;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function hasSize(): bool
    {
        return null !== $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function withSize(?int $size): FileInterface
    {
        $new = clone $this;
        $new->size = $size ?? null;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutSize(): FileInterface
    {
        $new = clone $this;
        $new->size = null;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function hasCreatedAt(): bool
    {
        return null !== $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function withCreatedAt(?DateTimeImmutable $createdAt): FileInterface
    {
        $new = clone $this;
        $new->createdAt = $createdAt ?? null;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutCreatedAt(): FileInterface
    {
        $new = clone $this;
        $new->createdAt = null;

        return $new;
    }
}
