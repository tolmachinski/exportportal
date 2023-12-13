<?php

namespace App\Filesystem;

/**
 * Creates and returns the right paths for users photo
 */
final class UserPhotoPathGenerator
{
    /**
     * Folder for the main photo
     */
    public static function userMainPhotoImagePath(int $userId, string $photo): string
    {
        return "/users/{$userId}/{$photo}";
    }
}
