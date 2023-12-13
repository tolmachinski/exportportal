<?php

namespace App\Filesystem;

/**
 * The path generator for filesystem.
 */
final class CrEventFilePathGenerator
{
    /**
     * Create path to the dirrectory uploaded to the directory for banners.
     */
    public static function eventPath(int $eventId, string $eventImage): string
    {
        return "cr_event_images/{$eventId}/{$eventImage}";
    }
}
