<?php

namespace App\Documents\Attachment;

use Doctrine\Common\Collections\ArrayCollection;

class AttachmentList extends ArrayCollection implements AttachmentCollectionInterface
{
    /**
     * Sets an attachment into collection.
     *
     * @param int   $key
     * @param mixed $element
     */
    public function set($key, $element)
    {
        if (!is_int($key)) {
            throw new \InvalidArgumentException(sprintf(
                "The argument 2 in '%s()' must be of type int, %s given",
                __METHOD__,
                gettype($key)
            ));
        }

        if (!$element instanceof AttachmentInterface) {
            throw new \InvalidArgumentException(sprintf(
                "The argument 2 in '%s()' must be an instance of '%s'",
                __METHOD__,
                AttachmentInterface::class
            ));
        }

        parent::set($key, $element);
    }

    /**
     * Adds an attachment into collection.
     *
     * @param mixed $element
     */
    public function add($element)
    {
        if (!$element instanceof AttachmentInterface) {
            throw new \InvalidArgumentException(sprintf(
                "The argument 1 in '%s()' must be an instance of '%s'",
                __METHOD__,
                AttachmentInterface::class
            ));
        }

        parent::add($element);
    }
}
