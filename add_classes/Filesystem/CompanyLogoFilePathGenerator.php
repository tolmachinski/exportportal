<?php

namespace App\Filesystem;

use App\Common\Contracts\Media\CompanyLogoThumb;

/**
 * Creates and returns the right paths for company logo.
 */
final class CompanyLogoFilePathGenerator
{
    // Folder for the arbitrary company directory.
    public static function file(int $companyId, string $path): string
    {
        return \sprintf("/company/{$companyId}/%s", \trim($path, '\\/'));
    }

    /**
     * Folder for the company directory.
     */
    public static function directory(int $companyId): string
    {
        return "/company/{$companyId}";
    }

    /**
     * Folder for the main image.
     */
    public static function logoFolder(int $companyId): string
    {
        return "/company/{$companyId}/logo";
    }

    /**
     * Path to the main image.
     */
    public static function logoPath(int $companyId, string $fileName): string
    {
        return "/company/{$companyId}/logo/{$fileName}";
    }

    /**
     * Path to the thumb.
     */
    public static function thumbImage(int $companyId, string $fileName, CompanyLogoThumb $size): string
    {
        return "/company/{$companyId}/logo/{$size->fileNamePrefix()}{$fileName}";
    }

    /**
     * Path to the main image.
     */
    public static function shiperLogoPath(int $companyId, string $fileName): string
    {
        return "shippers/{$companyId}/logo/{$fileName}";
    }
}
