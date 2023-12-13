<?php

namespace App\Documents\Versioning;

use App\Documents\Attachment\AttachmentCollectionInterface;
use App\Documents\File\FileInterface;
use App\Documents\User\UserInterface;

class PendingVersion extends AbstractVersion implements PendingVersionInterface, ReplaceableVersionInterface
{
    use ReplaceableVersionTrait;

    /**
     * Creates new instance of the accepted version.
     */
    public function __construct(
        string $name,
        ?string $comment = null,
        ?UserInterface $manager = null,
        ?FileInterface $file = null,
        ?ContentContext $context = null,
        ?AttachmentCollectionInterface $attachments = null
    ) {
        parent::__construct(VersionTypesInterface::PENDING, $name, $comment, $manager, $file, $context ?? new ContentContext(), $attachments);

        $this->replacementAttempt = 0;
        $this->originalCreationDate = $this->creationDate;
    }

    /**
     * {@inheritdoc}
     */
    public static function createFromVersion(VersionInterface $version)
    {
        /** @var static $instance */
        $instance = parent::createFromVersion($version);
        if ($version instanceof ReplaceableVersionInterface) {
            if ($instance->hasOriginalCreationDate()) {
                $instance = $instance->withOriginalCreationDate($version->getOriginalCreationDate());
            }

            $instance = $instance->withReplacementAttempt($version->getReplacementAttempt());
        }

        return $instance;
    }
}
