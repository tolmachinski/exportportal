<?php

namespace App\Filesystem;

/**
 * Creates and returns the right paths for the category articles (images) folder
 */
final class CategoryArticlesI18nFilePathGenerator
{
    /**
     * Folder for the main image
     */
    public static function mainImageFolder(int $categoryId): string
    {
        return "/category_articles_i18n/{$categoryId}";
    }

    /**
     * Path to the main image
     */
    public static function mainImagePath(int $categoryId, string $fileName): string
    {
        return "/category_articles_i18n/{$categoryId}/{$fileName}";
    }

}
