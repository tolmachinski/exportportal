<?php

namespace App\Filesystem;

/**
 * The path generator for droplist.
 */
final class ItemDroplistFilePathGenerator
{
    /**
     * Folder for the main image.
     */
    public static function droplistDirectoryPath(int $itemId): string
    {
        return "droplist/{$itemId}";
    }

    /**
     * Path to the main image.
     */
    public static function droplistImagePath(int $itemId, string $fileName): string
    {
        return "droplist/{$itemId}/{$fileName}";
    }
}