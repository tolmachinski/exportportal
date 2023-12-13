<?php

namespace App\Filesystem;

/**
 * The path generator for filesystem.
 *
 * @author Alexei Tolmachinski
 */
final class UserFilePathGenerator
{
    /**
     * Create path to the directory of users Images.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $userId
     */
    public static function imagesUploadPath(int $userId): string
    {
        return "users/{$userId}/";
    }

    /**
     * Create path to the directory of users Images.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $userId
     */
    public static function imagesUploadFilePath(int $userId, string $fileName): string
    {
        return "users/{$userId}/{$fileName}";
    }

    /**
     * Create path to the directory of users Thumb Images.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $userId
     */
    public static function imagesThumbUploadFilePath(int $userId, string $fileName): string
    {
        return "users/{$userId}/thumb_1_{$fileName}";
    }
}
