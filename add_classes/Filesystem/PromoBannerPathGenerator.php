<?php

namespace App\Filesystem;

/**
 * The path generator for filesystem.
 *
 * @author Alexei Tolmachinski
 */
final class PromoBannerPathGenerator
{
    /**
     * Create path to the public directory of promo banners.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $sessionId
     * @param mixed $fileName
     */
    public static function publicPromoBannerPath(int $sessionId, string $fileName): string
    {
        return "promo_banners/{$sessionId}/{$fileName}";
    }

    /**
     * Create path to the public directory of promo banners.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $bannerId
     */
    public static function relativePromoBannerPath(int $bannerId): string
    {
        return "promo_banners/{$bannerId}/";
    }
}
