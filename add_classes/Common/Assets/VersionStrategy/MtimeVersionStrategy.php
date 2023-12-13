<?php

declare(strict_types=1);

namespace App\Common\Assets\VersionStrategy;

use InvalidArgumentException;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

final class MtimeVersionStrategy implements VersionStrategyInterface
{
    /**
     * The files base path.
     *
     * @var string
     */
    private $basePath;

    /**
     * The url version format.
     *
     * @var string
     */
    private $format;

    /**
     * @param string $format
     */
    public function __construct(string $basePath = null, ?string $format = null)
    {
        $this->basePath = \realpath($basePath ?: '/');
        $this->format = $format ?: '%s?%s';
        if (false === $this->basePath) {
            throw new InvalidArgumentException(\sprintf('The provided path "%s" does not exists.', $this->basePath));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion(string $path)
    {
        if (file_exists($path)) {
            clearstatcache(true, $path);

            return md5((string) filemtime($path));
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function applyVersion(string $path)
    {
        $versionized = sprintf($this->format, ltrim($path, '/'), $this->getVersion($this->getFullPath($path)));
        if ($path && '/' === $path[0]) {
            return "/{$versionized}";
        }

        return $versionized;
    }

    /**
     * Get full path to the file.
     */
    private function getFullPath(string $path): string
    {
        return $this->basePath . '/' . \ltrim($path, '/');
    }
}
