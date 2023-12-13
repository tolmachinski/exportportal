<?php

namespace App\Documents\File;

use DateTimeImmutable;
use Hoa\Mime\Mime;
use Ramsey\Uuid\UuidInterface;

class File implements FileInterface
{
    use FileTrait;

    /**
     * The file type.
     *
     * @var null|string
     */
    protected $type = FileTypesInterface::FILE;

    /**
     * Created the file instance.
     */
    public function __construct(
        UuidInterface $id,
        ?string $name,
        ?string $extension,
        ?int $size = 0,
        ?string $mime = null,
        ?string $originalName = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->size = $size ?? 0;
        $this->mime = $mime;
        $this->extension = $extension;
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
