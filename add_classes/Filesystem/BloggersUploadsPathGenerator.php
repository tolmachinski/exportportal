<?php

namespace App\Filesystem;

/**
 * The path generator for filesystem.
 *
 * @author Alexei Tolmachinski
 */
final class BloggersUploadsPathGenerator
{
    /**
     * Create path to the directory of Bloggers Uploads.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $articleId
     */
    public static function mainUploadPath(int $articleId): string
    {
        return "bloggers/{$articleId}/main/";
    }

    /**
     * Create path to the directory of text photos Bloggers Uploads.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $articleId
     */
    public static function inlineUploadPath(int $articleId): string
    {
        return "bloggers/{$articleId}/text_photos/";
    }

    /**
     * Create path to the public directory of Bloggers Uploads.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $articleId
     * @param mixed $fileName
     */
    public static function publicMainUploadPath(int $articleId, string $fileName): string
    {
        return "bloggers/uploads/{$articleId}/{$fileName}";
    }

    /**
     * Create path to the public directory of text photos Bloggers Uploads.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $articleId
     * @param mixed $fileName
     */
    public static function publicInlineUploadPath(int $articleId, string $fileName): string
    {
        return "bloggers/uploads/{$articleId}/text_photos/{$fileName}";
    }

    /**
     * Create path to the public directory of Bloggers ID.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $articleId
     */
    public static function publicIdUploadPath(int $articleId): string
    {
        return "bloggers/uploads/{$articleId}";
    }

    /**
     * Create path to the public directory of Bloggers ID.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $articleId
     */
    public static function gridThumb(int $articleId, string $fileName): string
    {
        return "bloggers/uploads/{$articleId}/thumb_200xR_{$fileName}";
    }
}
