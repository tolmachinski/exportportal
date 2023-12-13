<?php

namespace App\Filesystem;

use App\Common\Contracts\Media\SellerVideosPhotosThumb;

/**
 * Creates and returns the right paths for videos
 */
final class CompanyVideosFilePathGenerator
{
    /**
     * Folder for the main image.
     */
    public static function videosFolder(int $companyId): string
    {
        return "/company/{$companyId}/videos";
    }

    /**
     * Path to the main image.
     */
    public static function videosPath(int $companyId, string $fileName): string
    {
        return "/company/{$companyId}/videos/{$fileName}";
    }

    /**
     * Path to the thumb
     */
    public static function thumbImage(int $companyId, string $fileName, SellerVideosPhotosThumb $size): string
    {
        return "/company/{$companyId}/videos/{$size->fileNamePrefix()}{$fileName}";
    }
}
