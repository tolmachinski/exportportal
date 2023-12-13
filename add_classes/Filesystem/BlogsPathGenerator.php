<?php

namespace App\Filesystem;

use App\Common\Contracts\Blogs\BlogPostImageThumb;

/**
 * The path generator for filesystem.
 *
 * @author Alexei Tolmachinski
 */
final class BlogsPathGenerator
{
    /**
     * Create path to the file uploaded to the blogs directory file.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $userId
     * @param mixed $folderName
     * @param mixed $fileName
     */
    public static function inlineImageBlogsPath(int $userId, string $folderName, string $fileName): string
    {
        return "blogs/{$userId}/{$folderName}/text_photos/{$fileName}";
    }

    /**
     * Create path to the file uploaded to the public blogs directory file.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $userId
     * @param mixed $fileName
     */
    public static function publicImageBlogsPath(int $userId, string $fileName): string
    {
        return "blogs/{$userId}/{$fileName}";
    }

    /**
     * Create path to the originla blog post main image.
     */
    public static function originalImage(int $userId, string $fileName): string
    {
        return "blogs/{$userId}/original_{$fileName}";
    }

    /**
     * Create path to the file uploaded to the public blogs directory file.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $userId
     * @param mixed $fileName
     */
    public static function gridThumb(int $userId, string $fileName): string
    {
        return "blogs/{$userId}/thumb_364xR_{$fileName}";
    }

    /**
     * Create the path to the blog post image thumb.
     */
    public static function thumb(int $blogPostId, string $fileName, BlogPostImageThumb $thumb): string
    {
        return "blogs/{$blogPostId}/{$thumb->filePrefix()}_{$fileName}";
    }

    /**
     * Create path to the file uploaded to the public blogs directory.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $userId
     */
    public static function publicBlogsPath(int $userId): string
    {
        return "blogs/{$userId}/";
    }

    /**
     * Create path to the file uploaded to the public blogs directory file.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $userId
     * @param mixed $fileName
     */
    public static function publicInlineImageBlogsPath(int $userId, string $fileName): string
    {
        return "blogs/{$userId}/text_photos/{$fileName}";
    }

    /**
     * Create path to the file uploaded to the public blogs directory file.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $userId
     */
    public static function publicInlineBlogsPath(int $userId): string
    {
        return "blogs/{$userId}/text_photos/";
    }
}
