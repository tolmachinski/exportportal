<?php

namespace App\Filesystem;

use App\Common\Contracts\Media\CompanyPhotosThumb;

/**
 * Creates and returns the right paths for photos
 */
final class CompanyPhotosFilePathGenerator
{
    /**
     * Folder for the main image.
     */
    public static function photosFolder(int $companyId): string
    {
        return "/company/{$companyId}/pictures";
    }

    /**
     * Path to the main image.
     */
    public static function photosPath(int $companyId, string $fileName): string
    {
        return "/company/{$companyId}/pictures/{$fileName}";
    }

    /**
     * Path to the thumb
     */
    public static function thumbImage(int $companyId, string $fileName, CompanyPhotosThumb $size): string
    {
        return "/company/{$companyId}/pictures/{$size->fileNamePrefix()}{$fileName}";
    }
}
