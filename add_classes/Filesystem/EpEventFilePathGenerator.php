<?php

namespace App\Filesystem;

/**
 * The path generator for filesystem.
 */
final class EpEventFilePathGenerator
{
    /**
     * Create path to the file of gallery thumb image.
     */
    public static function galleryThumbImagePath(string $folder, string $fileName, EpEventGalleryImageThumb $size): string
    {
        return "ep_events/images/{$folder}/gallery/{$size->fileNamePrefix()}{$fileName}";
    }

    /**
     * Create path to the file of gallery image.
     */
    public static function galleryImagePath(string $folder, string $image): string
    {
        return "ep_events/images/{$folder}/gallery/{$image}";
    }

    /**
     * Create path to the dirrectory of gallery image.
     */
    public static function galleryFolderPath(string $folder): string
    {
        return "ep_events/images/{$folder}/gallery";
    }

    /**
     * Create path to the file of main image.
     */
    public static function mainImagePath(string $folder, string $image): string
    {
        return "ep_events/images/{$folder}/{$image}";
    }

    /**
     * Create path to the dirrectory of gallery image.
     */
    public static function mainFolderPath(string $folder): string
    {
        return "ep_events/images/{$folder}";
    }

    /**
     * Create path to the file of recomended image.
     */
    public static function recomendedImagePath(string $folder, string $image): string
    {
        return "ep_events/images/{$folder}/recomended/{$image}";
    }

    /**
     * Create path to the dirrectory of recomended image.
     */
    public static function recomendedFolderPath(string $folder): string
    {
        return "ep_events/images/{$folder}/recomended/";
    }

    /**
     * Create path to the image of main image thumb.
     */
    public static function mainImageThumbPath (string $folder, string $fileName, EpEventMainImageThumb $size): string
    {
        return "ep_events/images/{$folder}/{$size->fileNamePrefix()}{$fileName}";

    }
}
