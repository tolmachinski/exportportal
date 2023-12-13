<?php

declare(strict_types=1);

namespace App\Common\Media\Thumbnail\Provider;

use App\Common\Media\Thumbnail\VideoMissingThumbnailException;
use App\Common\Media\Thumbnail\VideoThumbnail;
use App\Common\Media\Thumbnail\VideoThumbnailInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

abstract class AbsrtactVideoThumbnailProvider implements ThumbnailProviderInterface
{
    /**
     * The HTTP client instance.
     */
    protected ClientInterface $httpClient;

    /**
     * The list of patterns to search video ID.
     */
    protected array $patterns = [];

    public function __construct(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * {@inheritDoc}
     */
    public function getThumbnail(string $url): VideoThumbnailInterface
    {
        if (null === ($videoId = $this->extractVideoId($url))) {
            throw new RuntimeException(
                \sprintf('Cannot read the ID value form the video "%s"', $url)
            );
        }

        foreach ($this->prepareThumbnailUrls($videoId) as $thumbnailUrl) {
            if (null === ($imageStream = $this->getImageStream($thumbnailUrl))) {
                continue;
            }

            return new VideoThumbnail($thumbnailUrl, $this->getSource(), $videoId, $url, $imageStream);
        }


        throw new VideoMissingThumbnailException($url, $this->getSource(), $videoId);
    }

    /**
     * {@inheritDoc}
     */
    public function supports(string $url): bool
    {
        return null !== $this->extractVideoId($url);
    }

    /**
     * Returns the name of the video source.
     */
    abstract protected function getSource(): string;

    /**
     * Returns the list of the possible thumnail URLs.
     *
     * @return string[]
     */
    abstract protected function prepareThumbnailUrls(string $videoId): array;

    /**
     * Reads te remote image and returns the stream.
     */
    protected function getImageStream(string $url): ?StreamInterface
    {
        try {
            return $this->httpClient->send(new Request('GET', $url))->getBody();
        } catch (GuzzleException $e) {
            return null;
        }
    }

    /**
     * Returns the video ID from the provided URL.
     */
    private function extractVideoId(string $url): ?string
    {
        foreach ($this->patterns as $pattern) {
            if (!(bool) preg_match($pattern, $url, $matches)) {
                continue;
            }

            return $matches[1] ?? null;
        }

        return null;
    }
}
