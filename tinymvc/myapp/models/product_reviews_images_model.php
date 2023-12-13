<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Product_Reviews_Images model.
 */
final class Product_Reviews_Images_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'item_reviews_images';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'ITEM_REVIEWS_IMAGES';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'review_id' => Types::INTEGER,
        'id'        => Types::INTEGER,
    ];

    /**
     * Scope query by images IDs.
     *
     * @param QueryBuilder $builder
     * @param array $imagesIds
     *
     * @return void
     */
    protected function scopeIds(QueryBuilder $builder, array $imagesIds): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->table}`.`id`",
                array_map(
                    fn ($index, $imageId) => $builder->createNamedParameter(
                        (int) $imageId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("imageID_{$index}")
                    ),
                    array_keys($imagesIds),
                    $imagesIds
                )
            )
        );
    }

    /**
     * Scope a query to filter by reviewId.
     */
    protected function scopeReviewId(QueryBuilder $builder, int $reviewId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`review_id`",
                $builder->createNamedParameter($reviewId, ParameterType::INTEGER, $this->nameScopeParameter('reviewId'))
            )
        );
    }

    /**
     * Scope query by reviews IDs.
     *
     * @param QueryBuilder $builder
     * @param array $reviewsIds
     *
     * @return void
     */
    protected function scopeReviewsIds(QueryBuilder $builder, array $reviewsIds): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->table}`.`review_id`",
                array_map(
                    fn ($index, $reviewId) => $builder->createNamedParameter(
                        (int) $reviewId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("reviewID_{$index}")
                    ),
                    array_keys($reviewsIds),
                    $reviewsIds
                )
            )
        );
    }
}

// End of file product_reviews_images_model.php
// Location: /tinymvc/myapp/models/product_reviews_images_model.php
