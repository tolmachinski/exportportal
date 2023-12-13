<?php

declare(strict_types=1);

namespace App\Common\Media\Thumbnail;

use App\Common\Media\Thumbnail\Provider\ThumbnailProviderInterface;
use RuntimeException;

final class ThumbnailReader implements ThumbnailReaderInterface
{
    /**
     * The list of thumbnauil providers.
     *
     * @var ThumbnailProviderInterface[]
     */
    private array $providers = [];

    public function __construct(array $providers = [])
    {
        foreach ($providers as $provider) {
            if (!$provider instanceof ThumbnailProviderInterface) {
                throw new RuntimeException(
                    \sprintf(
                        'Only instances of the %s are accepted.',
                        ThumbnailProviderInterface::class
                    )
                );
            }

            $this->providers[] = $provider;
        }
    }

    /**
     * Adds one thumbnail provider.
     */
    public function addProvider(ThumbnailProviderInterface $provider): void
    {
        $this->providers[] = $provider;
    }

    /**
     * Removes all existing providers.
     */
    public function removeProviders(): void
    {
        $this->providers = [];
    }

    /**
     * Reads the thumbnail.
     */
    public function readThumbnail(string $url): ThumbnailInterface
    {
        $videoUrl = $this->normalizeUrl($url);
        foreach ($this->providers as $provider) {
            if (!$provider->supports($videoUrl)) {
                continue;
            }

            return $provider->getThumbnail($videoUrl);
        }

        throw new NoSupportedProvidersException($url);
    }

    /**
     * Normalizes the URL.
     */
    private function normalizeUrl(string $url): string
    {
        if (!preg_match('/^(http|https)\:\/\//i', $url)) {
            $url = 'https://' . $url;
        }

        return $url;
    }
}
