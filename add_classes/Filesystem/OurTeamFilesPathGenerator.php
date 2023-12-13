<?php

namespace App\Filesystem;

/**
 * The path generator for filesystem.
 *
 */
final class OurTeamFilesPathGenerator

{
    /**
     * Create path to the file uploaded to the public our team folder.
     * The path is created deterministically - for the same filename the path always be the same.
     */
    public static function defaultPublicImagePath(string $fileName): string
    {
        return "our_team/{$fileName}";
    }

}
