<?php

declare(strict_types=1);

namespace App\Common\Media\Thumbnail;

interface ThumbnailInterface
{
    /**
     * Returns the thumbnail contents in a string.
     *
     * @throws \RuntimeException if unable to read or an error occurs while reading
     */
    public function getContents(): string;
}
