<?php

namespace App\Documents\Attachment;

interface AttachmentCollectionAwareInterface
{
    /**
     * Checks if attachments exists.
     */
    public function hasAttachments(): bool;

    /**
     * Returns the attachments if they exists.
     */
    public function getAttachments(): ?AttachmentCollectionInterface;

    /**
     * Returns an instance with the specified attachments.
     *
     * @return static
     */
    public function withAttachments(AttachmentCollectionInterface $attachments);

    /**
     * Return an instance without attachments.
     *
     * @return static
     */
    public function withoutAttachments();
}
