<?php

namespace App\Filesystem;

/**
 * The path generator for filesystem.
 *
 * @author Alexei Tolmachinski
 */
final class VideoThumbsPathGenerator
{
    /**
     * Create path to the public directory of companies video thumbs.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $companyId
     */
    public static function publicUploadPath(int $companyId): string
    {
        return "company/{$companyId}/videos";
    }

    /**
     * Create path to the public directory of companies video thumbs.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $companyId
     * @param mixed $fileName
     */
    public static function publicImageUploadPath(int $companyId, string $fileName): string
    {
        return "company/{$companyId}/videos/{$fileName}";
    }

    /**
     * Create path to the public directory of video thumbs.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $companyId
     * @param mixed $fileName
     */
    public static function publicVideoImageUploadPath(string $fileName): string
    {
        return "videos/{$fileName}";
    }
}
