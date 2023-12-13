<?php

namespace App\Documents\Attachment;

class AbstractAttachment implements AttachmentInterface
{
    /**
     * The type of the attachment.
     *
     * @var string
     */
    private $type;

    /**
     * Creates the atttachments instance.
     */
    public function __construct(string $type)
    {
        $this->type = $type;
    }
}
