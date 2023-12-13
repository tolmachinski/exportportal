<?php

namespace App\Documents\Attachment;

trait AttachmentCollectionAwareTrait
{
    /**
     * The attachments collection.
     *
     * @var null|AttachmentCollectionInterface
     */
    private $attachments;

    /**
     * Checks if attachments exists.
     */
    public function hasAttachments(): bool
    {
        return null !== $this->attachments && !$this->attachments->isEmpty();
    }

    /**
     * Returns the attachments if they exists.
     */
    public function getAttachments(): ?AttachmentCollectionInterface
    {
        return $this->attachments;
    }

    /**
     * Returns an instance with the specified attachments.
     *
     * @return static
     */
    public function withAttachments(AttachmentCollectionInterface $attachments)
    {
        $new = clone $this;
        $new->attachments = $attachments;

        return $new;
    }

    /**
     * Return an instance without attachments.
     *
     * @return static
     */
    public function withoutAttachments()
    {
        $new = clone $this;
        $new->attachments = null;

        return $new;
    }
}
