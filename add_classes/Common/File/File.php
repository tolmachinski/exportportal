<?php

declare(strict_types=1);

namespace App\Common\File;

use Symfony\Component\HttpFoundation\File\File as BaseFile;

class File extends BaseFile
{

    /**
     * Returns the uri to the file.
     *
     * @param string|null $basePath
     * @param string|null $baseUri
     *
     * @return string
     */
    public function getUri(?string $basePath = null, ?string $baseUri = null): string
    {
        $basePath = null !== $basePath ? realpath($basePath) : getcwd();
        $filePath = $this->getRealPath();

        return ($baseUri ?? '/') . ltrim(
            str_replace('\\', '/', substr($filePath, mb_strlen($basePath))),
            '/'
        );
    }
}
