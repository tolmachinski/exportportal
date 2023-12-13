<?php

namespace App\Documents\Versioning;

use App\Documents\Attachment\AttachmentCollectionInterface;
use App\Documents\File\FileInterface;
use App\Documents\User\UserInterface;
use DateTimeImmutable;

class AcceptedVersion extends AbstractVersion implements AcceptedVersionInterface, ExpiringVersionInterface
{
    use AcceptedVersionTrait;
    use ExpiringVersionTrait;

    /**
     * Creates new instance of the accepted version.
     */
    public function __construct(
        string $name,
        ?string $comment = null,
        ?UserInterface $manager = null,
        ?FileInterface $file = null,
        ?AttachmentCollectionInterface $attachments = null,
        ?DateTimeImmutable $expirationDate = null
    ) {
        parent::__construct(VersionTypesInterface::ACCEPTED, $name, $comment, $manager, $file, new ContentContext(), $attachments);

        $this->expirationDate = $expirationDate;
        $this->acceptanceDate = new \DateTimeImmutable();
    }

    /**
     * {@inheritdoc}
     */
    public static function createFromVersion(VersionInterface $version)
    {
        /** @var static $instance */
        $instance = parent::createFromVersion($version);
        if (
            $version instanceof AcceptedVersionInterface
            && $version->hasAcceptanceDate()
        ) {
            $instance = $instance->withAcceptanceDate($version->getAcceptanceDate());
        }
        if (
            $version instanceof ExpiringVersionInterface
            && $version->hasExpirationDate()
        ) {
            $instance = $instance->withExpirationDate($version->getExpirationDate());
        }

        return $instance;
    }
}
