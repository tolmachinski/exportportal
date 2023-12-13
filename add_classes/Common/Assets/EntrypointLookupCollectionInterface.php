<?php

declare(strict_types=1);

namespace App\Common\Assets;

interface EntrypointLookupCollectionInterface
{
    /**
     * Determines if the EntrypointLookupInterface exists for the given build.
     */
    public function hasEntrypointLookup(?string $buildName = null): bool;

    /**
     * Retrieve the EntrypointLookupInterface for the given build.
     *
     * @throws UndefinedBuildException if the build does not exist
     */
    public function getEntrypointLookup(?string $buildName = null): EntrypointLookupInterface;
}
