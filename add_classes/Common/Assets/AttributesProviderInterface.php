<?php

declare(strict_types=1);

namespace App\Common\Assets;

interface AttributesProviderInterface
{
    /**
     * Get list of default attributes.
     */
    public function getDefaultAttributes(): array;

    /**
     * Set the list of default attributes.
     */
    public function setDefaultAttributes(array $attributes): void;

    /**
     * Merges the list of default attributes.
     */
    public function mergeDefaultAttributes(array $attributes): void;
}
