<?php

namespace App\Documents\Versioning;

use Doctrine\Common\Collections\Collection;

interface VersionCollectionInterface extends Collection
{
    /**
     * Replaces one element with another.
     */
    public function replace(VersionInterface $element, VersionInterface $replacement): bool;
}
