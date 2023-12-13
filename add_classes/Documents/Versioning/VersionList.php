<?php

namespace App\Documents\Versioning;

use Doctrine\Common\Collections\ArrayCollection;

class VersionList extends ArrayCollection implements VersionCollectionInterface
{
    /**
     * {@inheritdoc}
     *
     * @param int                                        $key
     * @param \App\Documents\Versioning\VersionInterface $value
     */
    public function set($key, $value)
    {
        if (!is_int($key)) {
            throw new \InvalidArgumentException(sprintf(
                "The argument 2 in '%s()' must be of type int, %s given",
                __METHOD__,
                gettype($key)
            ));
        }

        if (!$value instanceof VersionInterface) {
            throw new \InvalidArgumentException(sprintf(
                "The argument 2 in '%s()' must be an instance of '%s'",
                __METHOD__,
                VersionInterface::class
            ));
        }

        parent::set($key, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @param \App\Documents\Versioning\VersionInterface $element
     */
    public function add($element)
    {
        if (!$element instanceof VersionInterface) {
            throw new \InvalidArgumentException(sprintf(
                "The argument 1 in '%s()' must be an instance of '%s'",
                __METHOD__,
                VersionInterface::class
            ));
        }

        parent::add($element);
    }

    /**
     * {@inheritdoc}
     */
    public function replace(VersionInterface $element, VersionInterface $replacement): bool
    {
        $key = $this->indexOf($element);
        if (false === $key) {
            return false;
        }

        $this->set($key, $replacement);

        return true;
    }
}
