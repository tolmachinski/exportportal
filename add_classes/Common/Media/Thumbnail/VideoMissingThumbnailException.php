<?php

declare(strict_types=1);

namespace App\Common\Media\Thumbnail;

final class VideoMissingThumbnailException extends \RuntimeException
{
    /**
     * The ID of the video.
     */
    private string $videoId;

    /**
     * The name of the source of the video.
     */
    private string $source;

    /**
     * The URL of the video.
     */
    private string $videoUrl;

    public function __construct(string $videoUrl, string $source, string $videoId, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(
            \sprintf('The thumbnail for the video "%s" is missing', $videoUrl),
            $code,
            $previous
        );

        $this->source = $source;
        $this->videoId = $videoId;
        $this->videoUrl = $videoUrl;
    }

    /**
     * Get the ID of the video.
     */
    public function getVideoId(): string
    {
        return $this->videoId;
    }

    /**
     * Get the name of the source of the video.
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * Get the URL of the video.
     */
    public function getVideoUrl(): string
    {
        return $this->videoUrl;
    }
}
