<?php

namespace App\Documents\Versioning;

use App\Documents\Attachment\AttachmentCollectionAwareInterface;
use App\Documents\Attachment\AttachmentCollectionAwareTrait;
use App\Documents\Attachment\AttachmentCollectionInterface;
use App\Documents\File\FileAwareInterface;
use App\Documents\File\FileAwareTrait;
use App\Documents\File\FileInterface;
use App\Documents\User\ManagerAwareInterface;
use App\Documents\User\ManagerAwareTrait;
use App\Documents\User\UserInterface;

abstract class AbstractVersion implements VersionInterface, ManagerAwareInterface, FileAwareInterface, AttachmentCollectionAwareInterface
{
    use VersionTrait;
    use FileAwareTrait;
    use ManagerAwareTrait;
    use AttachmentCollectionAwareTrait;

    /**
     * The type of the version.
     *
     * @var string
     */
    private $type;

    /**
     * Creates new instance of the version.
     */
    public function __construct(
        string $type,
        string $name,
        ?string $comment = null,
        ?UserInterface $manager = null,
        ?FileInterface $file = null,
        ?ContentContext $context = null,
        ?AttachmentCollectionInterface $attachments = null
    ) {
        $this->type = $type;
        $this->name = $name;
        $this->file = $file;
        $this->context = $context ?? new ContentContext();
        $this->manager = $manager;
        $this->comment = $comment;
        $this->attachments = $attachments;
        $this->creationDate = new \DateTimeImmutable();
    }

    /**
     * Creates the new version from another version.
     *
     * @return static
     */
    public static function createFromVersion(VersionInterface $version)
    {
        /** @var static $instance */
        $instance = (new static($version->getName(), $version->getComment()))->withContext($version->getContext());
        if ($version instanceof ManagerAwareInterface && $version->hasManager()) {
            $instance = $instance->withManager($version->getManager());
        }
        if ($version instanceof FileAwareInterface && $version->hasFile()) {
            $instance = $instance->withFile($version->getFile());
        }
        if (
            $version instanceof AttachmentCollectionAwareInterface
            && $version->hasAttachments()
            && $version->getAttachments()->count() > 0
        ) {
            $instance = $instance->withAttachments($version->getAttachments());
        }

        return $instance->withCreationDate($version->getCreationDate());
    }
}
