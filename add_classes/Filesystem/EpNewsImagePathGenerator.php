<?php

namespace App\Filesystem;

/**
 * The path generator for filesystem.
 *
 */
final class EpNewsImagePathGenerator

{
    /**
     * Create path to the file uploaded to the public ep_news folder.
     * The path is created deterministically - for the same filename the path always be the same.
     */
    public static function defaultPublicImagePath(string $fileName): string
    {
        return "ep_news/{$fileName}";
    }

    /**
     * Create path to the file uploaded to the public ep_news folder.
     * The path is created deterministically - for the same filename the path always be the same.
     */
    public static function thumbPublicImagePath(string $fileName): string
    {
        return "ep_news/thumb_Rx195_{$fileName}";
    }
}
