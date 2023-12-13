<?php

namespace App\Filesystem;

/**
 * The path generator for filesystem.
 *
 * @author Alexei Tolmachinski
 */
final class PartnersUploadsPathGenerator
{
    /**
     * Create path to the public directory of partners.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $fileName
     */
    public static function publicPromoBannerPath(string $fileName): string
    {
        return "partners/{$fileName}";
    }

    /**
     * Create path to the public directory of partners.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $fileName
     */
    public static function publicPromoBannerDirectory(): string
    {
        return "partners/";
    }
}
