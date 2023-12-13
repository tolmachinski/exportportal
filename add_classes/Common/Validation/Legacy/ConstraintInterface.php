<?php

namespace App\Common\Validation\Legacy;

use App\Common\Validation\ConstraintInterface as BaseConstraintInterface;

interface ConstraintInterface extends BaseConstraintInterface
{
    /**
     * Returns the legacy validator metadata.
     */
    public function getMetadata(): array;

    /**
     * Sets the legacy validator metadata.
     */
    public function setMetadata(array $metadata = array()): void;
}
