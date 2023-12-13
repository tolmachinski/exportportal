<?php

namespace App\Filesystem;

use App\Common\Contracts\Media\DisputePhotoThumb;

/**
 * Creates and returns the right paths for the disputes folder
 */
final class DisputeFilePathGenerator
{
    /**
     * Folder for the imagess
     */
    public static function imageFolder(int $orderId): string
    {
        return "/disputes/{$orderId}";
    }

    /**
     * Path to the image
     */
    public static function imagePath(int $orderId, string $fileName): string
    {
        return "/disputes/{$orderId}/{$fileName}";
    }

    /**
     * Folder for the videos
     */
    public static function videoFolder(int $orderId): string
    {
        return "/disputes/{$orderId}/videos";
    }

    /**
     * Path to the video image
     */
    public static function videoImage(int $orderId, string $fileName): string
    {
        return "/disputes/{$orderId}/videos/{$fileName}";
    }

    /**
     * Path to the thumb
     */
    public static function thumbImage(int $orderId, string $fileName, DisputePhotoThumb $size): string
    {
        return "/disputes/{$orderId}/{$size->fileNamePrefix()}{$fileName}";
    }
}
