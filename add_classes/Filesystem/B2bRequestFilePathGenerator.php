<?php

namespace App\Filesystem;

/**
 * Creates and returns the right paths for the request files (images) folder
 *
 * @author Tatiana Bendiucov
 */
final class B2bRequestFilePathGenerator
{
    /**
     * Folder for the main image
     */
    public static function b2bRequestMainImageFolder(int $requestId): string
    {
        return "/b2b/{$requestId}";
    }

    /**
     * Folder for the photos
     */
    public static function b2bRequestPhotosFolder(int $requestId): string
    {
        return "/b2b/{$requestId}/images";
    }

    /**
     * Path to the main image
     */
    public static function b2bRequestMainImage(int $requestId, string $fileName): string
    {
        return "/b2b/{$requestId}/{$fileName}";
    }

    /**
     * Path to the photo
     */
    public static function b2bRequestPhoto(int $requestId, string $fileName): string
    {
        return "/b2b/{$requestId}/images/{$fileName}";
    }
}
