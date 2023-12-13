<?php

namespace App\Filesystem;

/**
 * The path generator for filesystem.
 */
final class EpEventPartnersFilePathGenerator
{
    /**
     * Create path to the file of main image.
     */
    public static function mainImagePath(string $folder, string $image): string
    {
        return "ep_events/partners/{$folder}/{$image}";
    }

    /**
     * Create path to the dirrectory of main image.
     */
    public static function mainFolderPath(string $folder): string
    {
        return "ep_events/partners/{$folder}";
    }
}