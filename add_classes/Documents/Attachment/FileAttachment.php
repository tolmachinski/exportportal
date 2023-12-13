<?php

namespace App\Documents\Attachment;

use App\Documents\File\FileInterface;
use App\Documents\File\FileTrait;
use DateTimeImmutable;
use Hoa\Mime\Mime;
use Ramsey\Uuid\UuidInterface;

class FileAttachment extends AbstractAttachment implements FileInterface
{
    use FileTrait;

    /**
     * Creates the atttachments instance.
     */
    public function __construct(
        UuidInterface $id,
        ?string $name,
        ?string $extension,
        ?int $size = 0,
        ?string $mime = null,
        ?string $originalName = null
    ) {
        parent::__construct(AttachmentTypesInterface::FILE_ATTACHMENT);

        $this->id = $id;
        $this->name = $name;
        $this->extension = $extension;
        $this->size = $size ?? 0;
        $this->mime = $mime;
        $this->originalName = $originalName;
        $this->uploadDate = new DateTimeImmutable();
        if (null === $this->mime) {
            $this->mime = Mime::getMimeFromExtension($this->extension);
        }
        if (!empty($this->mime)) {
            list($media) = explode('/', $this->mime);
            $this->media = $media;
        }
    }
}
