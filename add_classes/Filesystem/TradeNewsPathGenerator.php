<?php

namespace App\Filesystem;

/**
 * The path generator for filesystem.
 *
 * @author Alexei Tolmachinski
 */
final class TradeNewsPathGenerator
{
    /**
     * Create path to the directory of Trade News.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $postId
     */
    public static function mainUploadPath(int $postId): string
    {
        return "trade_news/{$postId}/";
    }
    /**
     * Create path to the directory of Trade News.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $postId
     */
    public static function inlineUploadPath(int $postId): string
    {
        return "trade_news/{$postId}/text_photos/";
    }
    /**
     * Create path to the directory of Trade News.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $postId
     */
    public static function inlineImageUploadPath(int $postId, string $imageName): string
    {
        return "trade_news/{$postId}/text_photos/{$imageName}";
    }
}
