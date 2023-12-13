<?php

declare(strict_types=1);

namespace App\Common\Media\Thumbnail;

final class NoSupportedProvidersException extends \RuntimeException
{
    /**
     * The URL of the video.
     */
    private string $videoUrl;

    public function __construct(string $videoUrl, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(
            \sprintf('The reader has no thumnail providers that can process the video url "%s"', $videoUrl),
            $code,
            $previous
        );

        $this->videoUrl = $videoUrl;
    }

    /**
     * Get the URL of the video.
     */
    public function getVideoUrl(): string
    {
        return $this->videoUrl;
    }
}
