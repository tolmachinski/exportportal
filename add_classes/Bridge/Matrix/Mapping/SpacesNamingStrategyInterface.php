<?php

declare(strict_types=1);

namespace App\Bridge\Matrix\Mapping;

/**
 * A set of rules for matrix spaces names.
 */
interface SpacesNamingStrategyInterface
{
    /**
     * Returns the space name for simple name.
     */
    public function spaceName(string $name): string;

    /**
     * Returns the space alias for name.
     */
    public function spaceAlias(string $spaceName): string;
}
