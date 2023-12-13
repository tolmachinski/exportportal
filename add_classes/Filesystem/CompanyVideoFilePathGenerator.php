<?php

namespace App\Filesystem;

/**
 * Creates and returns the right paths for video
 */
final class CompanyVideoFilePathGenerator
{
    /**
     * Folder for the main image.
     */
    public static function videoFolder(int $companyId): string
    {
        return "/company/{$companyId}";
    }

    /**
     * Path to the main image.
     */
    public static function videoPath(int $companyId, string $fileName): string
    {
        return "/company/{$companyId}/{$fileName}";
    }
}
