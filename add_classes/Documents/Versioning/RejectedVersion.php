<?php

namespace App\Documents\Versioning;

use App\Documents\Attachment\AttachmentCollectionInterface;
use App\Documents\File\FileInterface;
use App\Documents\User\UserInterface;

class RejectedVersion extends AbstractVersion implements RejectedVersionInterface
{
    use RejectedVersionTrait;

    /**
     * Creates new instance of the accepted version.
     *
     * @param null|mixed $reasonCode
     * @param null|mixed $reason
     */
    public function __construct(
        string $name,
        ?string $comment = null,
        ?UserInterface $manager = null,
        ?FileInterface $file = null,
        ?AttachmentCollectionInterface $attachments = null,
        $reasonCode = null,
        $reason = null
    ) {
        parent::__construct(VersionTypesInterface::REJECTED, $name, $comment, $manager, $file, new ContentContext(), $attachments);

        $this->reason = $reason;
        $this->reasonCode = $reasonCode;
        $this->rejectionDate = new \DateTimeImmutable();
    }

    /**
     * {@inheritdoc}
     */
    public static function createFromVersion(VersionInterface $version)
    {
        /** @var static $instance */
        $instance = parent::createFromVersion($version);
        if ($version instanceof RejectedVersionInterface) {
            if ($version->hasReasonCode()) {
                $instance = $instance->withReasonCode($version->getReasonCode());
            }

            if ($version->hasReason()) {
                $instance = $instance->withReason($version->getReason());
            }

            if ($version->hasRejectionDate()) {
                $instance = $instance->withRejectionDate($version->getRejectionDate());
            }
        }

        return $instance;
    }
}
