<?php

declare(strict_types=1);

namespace App\Common\Media\Thumbnail\Provider;

use App\Common\Media\Thumbnail\ThumbnailInterface;
use App\Common\Media\Thumbnail\VideoMissingThumbnailException;

interface ThumbnailProviderInterface
{
    /**
     * Get the thumnail for the URL.
     *
     * @throws VideoMissingThumbnailException if thumbnail is missing
     */
    public function getThumbnail(string $url): ThumbnailInterface;

    /**
     * Determine if the provider supports the URL.
     */
    public function supports(string $url): bool;
}
