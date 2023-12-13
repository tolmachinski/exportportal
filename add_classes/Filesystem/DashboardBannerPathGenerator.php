<?php

namespace App\Filesystem;

/**
 * The path generator for dashboard banner.
 *
 * @author Anton Zencenco
 */
final class DashboardBannerPathGenerator
{
    /**
     * Generate path to the banner image.
     */
    public static function bannerImage(string $fileName): string
    {
        return "/dashboard_banners/{$fileName}";
    }

    /**
     * Generate path to the banner files directory.
     */
    public static function bannerDirectory(int $bannerId): string
    {
        return "/dashboard_banners/{$bannerId}";
    }
}
