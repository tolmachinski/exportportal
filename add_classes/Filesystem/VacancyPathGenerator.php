<?php

namespace App\Filesystem;

/**
 * The path generator for filesystem.
 *
 * @author Alexei Tolmachinski
 */
final class VacancyPathGenerator
{
    /**
     * Create path to the directory of vacancy Images.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $vacancyId
     */
    public static function defaultUploadPath(int $vacancyId): string
    {
        return "vacancies/{$vacancyId}/";
    }
    /**
     * Create path to the directory of vacancy Images.
     * The path is created deterministically - for the same filename the path always be the same.
     *
     * @param mixed $vacancyId
     */
    public static function imageUploadPath(int $vacancyId, string $fileName): string
    {
        return "vacancies/{$vacancyId}/{$fileName}";
    }
}
