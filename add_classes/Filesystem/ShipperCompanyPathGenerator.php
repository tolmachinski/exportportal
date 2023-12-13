<?php

namespace App\Filesystem;

/**
 * The path generator for filesystem.
 *
 * @author Alexei Tolmachinski
 */
final class ShipperCompanyPathGenerator
{
    /**
     * Create path to the default directory of shippers company Image.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $shipperCompanyId
     */
    public static function publicImgDefaultPath(int $shipperCompanyId): string
    {
        return "shippers/{$shipperCompanyId}/pictures/";
    }

    /**
     * Create path to the default directory of shippers company Logo.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $shipperCompanyId
     */
    public static function publicLogoDefaultPath(int $shipperCompanyId): string
    {
        return "shippers/{$shipperCompanyId}/logo/";
    }

}
