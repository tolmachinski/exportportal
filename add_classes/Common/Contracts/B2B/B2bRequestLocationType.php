<?php

declare(strict_types=1);

namespace App\Common\Contracts\B2B;

use ExportPortal\Enum\EnumCase;
use UnhandledMatchError;

/**
 * B2b Location Types
 */
final class B2bRequestLocationType extends EnumCase
{
    public const GLOBALLY = 'globally';
    public const COUNTRY = 'country';
    public const RADIUS = 'radius';

    /**
     * Get the label for enum case.
     */
    public static function getLabel(self $value): string
    {
        switch ($value) {
            case self::GLOBALLY(): return 'Globally';
            case self::COUNTRY(): return 'Country';
            case self::RADIUS(): return 'Radius (km from my location)';
        }

        throw new UnhandledMatchError('The provided type is not supported.');
    }

    /**
     * Get all the cases of the current enum case as key => label.
     */
    public static function getAllLocationWithLabels(): array
    {
        $locationTypes = [];
        foreach (self::cases() as $type) {
            try {
                $locationTypes[$type->value] = self::getLabel($type);
            } catch (UnhandledMatchError $e) {
                //silent fail
            }
        }

        return $locationTypes;
    }
}
