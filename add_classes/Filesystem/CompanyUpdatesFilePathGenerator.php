<?php

namespace App\Filesystem;

use App\Common\Contracts\Media\SellerUpdatesPhotoThumb;

/**
 * Creates and returns the right paths for updates
 */
final class CompanyUpdatesFilePathGenerator
{
    /**
     * Folder for the main image.
     */
    public static function updatesFolder(int $companyId): string
    {
        return "/company/{$companyId}/updates";
    }

    /**
     * Path to the main image.
     */
    public static function updatesPath(int $companyId, string $fileName): string
    {
        return "/company/{$companyId}/updates/{$fileName}";
    }

    /**
     * Path to the thumb
     */
    public static function thumbImage(int $companyId, string $fileName, SellerUpdatesPhotoThumb $size): string
    {
        return "/company/{$companyId}/updates/{$size->fileNamePrefix()}{$fileName}";
    }
}
