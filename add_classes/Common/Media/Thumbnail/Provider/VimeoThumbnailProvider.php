<?php

declare(strict_types=1);

namespace App\Common\Media\Thumbnail\Provider;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use JsonException;
use Psr\Http\Message\StreamInterface;

final class VimeoThumbnailProvider extends AbsrtactVideoThumbnailProvider
{
    /**
     * The list of patterns to search video ID.
     */
    protected array $patterns = [
        '/[http|https]+:\/\/(?:www\.|)vimeo\.com\/([a-zA-Z0-9_\-]+)(&.+)?/i',
        '/[http|https]+:\/\/player\.vimeo\.com\/video\/([a-zA-Z0-9_\-]+)(&.+)?/i',
    ];

    /**
     * Returns the list of the possible thumnail URLs.
     *
     * @return string[]
     */
    protected function prepareThumbnailUrls(string $videoId): array
    {
        return [
            "https://vimeo.com/api/v2/video/{$videoId}.json",
        ];
    }

    /**
     * Returns the name of the video source.
     */
    protected function getSource(): string
    {
        return 'vimeo';
    }

    /**
     * Reads te remote image and returns the stream.
     */
    protected function getImageStream(string $url): ?StreamInterface
    {
        try {
            $metadata = \json_decode(
                $this->httpClient->send(new Request('GET', $url))->getBody()->getContents(),
                true,
                512,
                \JSON_THROW_ON_ERROR
            );

            $imageUrl = $metadata[0]['thumbnail_large'] ?? $metadata[0]['thumbnail_medium'] ?? null;
            if (null === $imageUrl) {
                return null;
            }
            $imageParts = explode('_', $imageUrl);

            return parent::getImageStream($imageParts[0] . '_800.jpg');
        } catch (JsonException | GuzzleException $e) {
            return null;
        }
    }
}
