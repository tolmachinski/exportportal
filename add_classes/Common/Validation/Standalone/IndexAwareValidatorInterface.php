<?php

namespace App\Common\Validation\Standalone;

interface IndexAwareValidatorInterface
{
    /**
     * Applies the changes of the index index for the validator.
     */
    public function applyIndex(?int $index): void;
}
