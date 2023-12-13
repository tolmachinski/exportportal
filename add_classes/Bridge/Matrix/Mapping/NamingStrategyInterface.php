<?php

declare(strict_types=1);

namespace App\Bridge\Matrix\Mapping;

/**
 * A set of rules for determining the matrix user and profile room names.
 *
 * @deprecated Use UserNamingStrategyInterface and SpacesNamingStrategyInterface instead
 */
interface NamingStrategyInterface extends UserNamingStrategyInterface, SpacesNamingStrategyInterface
{
    // HIC SVNT DRACONES
}
