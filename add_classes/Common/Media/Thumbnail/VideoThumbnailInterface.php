<?php

declare(strict_types=1);

namespace App\Common\Media\Thumbnail;

interface VideoThumbnailInterface extends ThumbnailInterface
{
    /**
     * Returns the thumbnail url.
     *
     * @throws \RuntimeException if unable to read or an error occurs while reading
     */
    public function getUrl(): string;

    /**
     * Returns the video soruce.
     *
     * @throws \RuntimeException if unable to read or an error occurs while reading
     */
    public function getSource(): string;

    /**
     * Returns the source video ID.
     *
     * @throws \RuntimeException if unable to read or an error occurs while reading
     */
    public function getVideoId(): string;

    /**
     * Returns the source video URL.
     *
     * @throws \RuntimeException if unable to read or an error occurs while reading
     */
    public function getVideoUrl(): string;
}
