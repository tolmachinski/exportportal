<?php

namespace App\Filesystem;

/**
 * The path generator for product reviews.
 *
 * @author Anton Zencenco
 */
final class ProductReviewPathGenerator
{
    /**
     * Generate path to the preview image.
     */
    public static function reviewImage(int $reviewId, string $fileName): string
    {
        return "/product_reviews/{$reviewId}/{$fileName}";
    }

    /**
     * Generate path to the preview files directory.
     */
    public static function reviewDirectory(int $reviewId): string
    {
        return "/product_reviews/{$reviewId}";
    }
}
