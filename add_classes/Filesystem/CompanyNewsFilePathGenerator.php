<?php

namespace App\Filesystem;

use App\Common\Contracts\Media\SellerNewsPhotoThumb;

/**
 * Creates and returns the right paths for news
 */
final class CompanyNewsFilePathGenerator
{
    /**
     * Folder for the main image.
     */
    public static function newsFolder(int $companyId): string
    {
        return "company/{$companyId}/news";
    }

    /**
     * Path to the main image.
     */
    public static function newsPath(int $companyId, string $fileName): string
    {
        return "company/{$companyId}/news/{$fileName}";
    }

    /**
     * Path to the thumb
     */
    public static function thumbImage(int $companyId, string $fileName, SellerNewsPhotoThumb $size): string
    {
        return "company/{$companyId}/news/{$size->fileNamePrefix()}{$fileName}";
    }
}
