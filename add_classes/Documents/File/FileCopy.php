<?php

declare(strict_types=1);

namespace App\Documents\File;

use Ramsey\Uuid\UuidInterface;

class FileCopy extends File
{
    /**
     * The file type.
     *
     * @var null|string
     */
    protected $type = FileTypesInterface::FILE_COPY;

    /**
     * The ID of the original file.
     *
     * @var UuidInterface
     */
    private $originalId;

    /**
     * Created the file instance.
     */
    public function __construct(UuidInterface $id, FileInterface $original)
    {
        parent::__construct(
            $id,
            $original->getName(),
            $original->getExtension(),
            $original->getSize(),
            $original->getMime(),
            $original->getOriginalName()
        );

        $this->originalId = $original->getId();
    }

    /**
     * Get the ID of the original file.
     *
     * @return UuidInterface
     */
    public function getOriginalId()
    {
        return $this->originalId;
    }

    /**
     * Returns an instance with the ID of the original file.
     */
    public function withOriginalId(UuidInterface $originalId): self
    {
        $new = clone $this;
        $new->originalId = $originalId;

        return $new;
    }

    /**
     * Returns an instance with the ID of the original file.
     */
    public function withoutOriginalId(): self
    {
        $new = clone $this;
        $new->originalId = null;

        return $new;
    }
}
