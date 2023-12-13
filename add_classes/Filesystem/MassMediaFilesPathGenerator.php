<?php

namespace App\Filesystem;

/**
 * The path generator for filesystem.
 *
 */
final class MassMediaFilesPathGenerator

{
    /**
     * Create path to the file uploaded to the public media folder.
     * The path is created deterministically - for the same filename the path always be the same.
     */
    public static function defaultMediaPublicImagePath($fileName): string
    {
        return "media/{$fileName}";
    }

    /**
     * Create path to the file uploaded to the public media folder.
     * The path is created deterministically - for the same filename the path always be the same.
     */
    public static function defaultNewsPublicImagePath($fileName): string
    {
        return "news/{$fileName}";
    }
}
