<?php

declare(strict_types=1);

namespace App\Common\Media\Thumbnail\Provider;

final class YoutubeThumbnailProvider extends AbsrtactVideoThumbnailProvider
{
    /**
     * The list of patterns to search video ID.
     */
    protected array $patterns = [
        '/[http|https]+:\/\/(?:www\.|)youtube\.com\/watch\?(?:.*)?v=([a-zA-Z0-9_\-]+)/i',
        '/[http|https]+:\/\/(?:www\.|)youtube\.com\/embed\/([a-zA-Z0-9_\-]+)/i',
        '/[http|https]+:\/\/(?:www\.|)youtu\.be\/([a-zA-Z0-9_\-]+)/i',
    ];

    /**
     * Returns the name of the video source.
     */
    protected function getSource(): string
    {
        return 'youtube';
    }

    /**
     * Returns the list of the possible thumnail URLs.
     *
     * @return string[]
     */
    protected function prepareThumbnailUrls(string $videoId): array
    {
        return [
            "http://i3.ytimg.com/vi/{$videoId}/maxresdefault.jpg",
            "https://img.youtube.com/vi/{$videoId}/0.jpg",
        ];
    }
}
