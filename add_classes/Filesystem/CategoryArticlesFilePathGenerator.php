<?php

namespace App\Filesystem;

/**
 * Creates and returns the right paths for the category articles (images) folder
 */
final class CategoryArticlesFilePathGenerator
{
    /**
     * Folder for the main image
     */
    public static function mainImageFolder(int $categoryId): string
    {
        return "/category_articles/{$categoryId}";
    }

    /**
     * Path to the main image
     */
    public static function mainImagePath(int $categoryId, string $fileName): string
    {
        return "/category_articles/{$categoryId}/{$fileName}";
    }

}
