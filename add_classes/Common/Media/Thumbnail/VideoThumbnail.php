<?php

declare(strict_types=1);

namespace App\Common\Media\Thumbnail;

use Psr\Http\Message\StreamInterface;

final class VideoThumbnail implements VideoThumbnailInterface
{
    /**
     * The video thumbnail URL.
     */
    private string $url;

    /**
     * The video source.
     */
    private string $source;

    /**
     * The original video ID.
     */
    private string $videoId;

    /**
     * The source video URL.
     */
    private string $videoUrl;

    /**
     * The thumbnail image stream.
     */
    private StreamInterface $stream;

    public function __construct(string $url, string $source, string $videoId, string $videoUrl, StreamInterface $stream)
    {
        $this->url = $url;
        $this->source = $source;
        $this->stream = $stream;
        $this->videoId = $videoId;
        $this->videoUrl = $videoUrl;
    }

    /**
     * Returns the thumbnail url.
     *
     * @throws \RuntimeException if unable to read or an error occurs while reading
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Returns the video soruce.
     *
     * @throws \RuntimeException if unable to read or an error occurs while reading
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * Returns the source video ID.
     *
     * @throws \RuntimeException if unable to read or an error occurs while reading
     */
    public function getVideoId(): string
    {
        return $this->videoId;
    }

    /**
     * Returns the source video URL.
     *
     * @throws \RuntimeException if unable to read or an error occurs while reading
     */
    public function getVideoUrl(): string
    {
        return $this->videoUrl;
    }

    /**
     * Returns the thumbnail contents in a string.
     *
     * @throws \RuntimeException if unable to read or an error occurs while reading
     */
    public function getContents(): string
    {
        $this->stream->rewind();

        return $this->stream->getContents();
    }
}
