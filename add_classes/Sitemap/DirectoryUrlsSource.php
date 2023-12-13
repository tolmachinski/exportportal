<?php

namespace App\Sitemap;

use ExportPortal\Contracts\Filesystem\FilesystemOperator;
use League\Flysystem\PathPrefixer;

class DirectoryUrlsSource implements UrlSourceInterface
{
    private FilesystemOperator $storage;
    private PathPrefixer $prefixer;
    private $siteUrl;
    private $listFiles;

    public function __construct(
        FilesystemOperator $storage,
        PathPrefixer $prefixer,
        $siteUrl,
        array $listFiles
    ) {
        $this->prefixer = $prefixer;
        $this->storage = $storage;
        $this->siteUrl = $siteUrl;
        $this->listFiles = $listFiles;
    }

    public function getUrls()
    {
        foreach ($this->listFiles as $filePath) {
            if ($this->storage->fileExists($filePath)) {
                yield str_replace('\\', '/', $this->siteUrl . $this->prefixer->prefixPath($filePath));
            }
        }
    }
}
