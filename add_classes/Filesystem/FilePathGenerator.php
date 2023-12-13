<?php

namespace App\Filesystem;

/**
 * The path generator for filesystem.
 *
 * @author Anton Zencenco
 */
final class FilePathGenerator
{
    /**
     * Create path to the file uploaded to the directory for uploads. The path is created deterministically - for the same filename the path always be the same.
     *
     * @deprecated `v2.37` in favor of `\App\Filesystem\FilePathGenerator::uploadedFile()`
     *
     * @uses App\Filesystem\FilePathGenerator::uploadedFile()
     */
    public static function makePathToUploadedFile(string $fileName): string
    {
        return static::uploadedFile($fileName);
    }

    /**
     * Creates the directory path for upladed file. The path is created deterministically - for the
     * same filename the path always be the same.
     */
    public static function uploadedFile(string $fileName): string
    {
        $nameHash = \md5($fileName);

        return \sprintf(
            'upload/%s/%s/%s/%s',
            \substr($nameHash, 0, 2),
            \substr($nameHash, 2, 2),
            \substr($nameHash, 4, 2),
            $fileName
        );
    }
}
