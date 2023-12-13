<?php

namespace App\Filesystem;

/**
 * The path generator for filesystem.
 *
 */
final class CountryStatisticFilePathGenerator

{
    /**
     * Create path to the file uploaded to the export_import_statistic directory.
     * The path is created deterministically - for the same filename the path always be the same.
     */
    public static function relativeImageUploadPath(string $fileName): string
    {
        return "export_import_statistic/{$fileName}";
    }
}
