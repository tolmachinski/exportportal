<?php

declare(strict_types=1);

namespace App\Common\Media\Thumbnail;

use App\Common\Media\Thumbnail\Provider\ThumbnailProviderInterface;

interface ThumbnailReaderInterface
{
    /**
     * Adds one thumbnail provider.
     */
    public function addProvider(ThumbnailProviderInterface $provider): void;

    /**
     * Removes all existing providers.
     */
    public function removeProviders(): void;

    /**
     * Reads the thumbnail.
     *
     * @throws NoSupportedProvidersException  if there are no providers to process the $url
     * @throws VideoMissingThumbnailException if thumbnail is missing
     */
    public function readThumbnail(string $url): ThumbnailInterface;
}
