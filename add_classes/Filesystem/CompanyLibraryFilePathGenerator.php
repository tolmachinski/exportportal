<?php

namespace App\Filesystem;

/**
 * Creates and returns the right paths for library
 */
final class CompanyLibraryFilePathGenerator
{
    /**
     * Folder for the main image.
     */
    public static function libraryFolder(int $companyId): string
    {
        return "/company/{$companyId}/library";
    }

    /**
     * Path to the main image.
     */
    public static function libraryPath(int $companyId, string $fileName): string
    {
        return "/company/{$companyId}/library/{$fileName}";
    }
}
