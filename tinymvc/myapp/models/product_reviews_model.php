<?php

declare(strict_types=1);

use App\Common\Contracts\ProductReview\ProductReviewStatus;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Product_Reviews model.
 */
final class Product_Reviews_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'item_reviews';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'ITEM_REVIEWS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_review';

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_ordered_item'       => Types::INTEGER,
        'count_minus'           => Types::INTEGER,
        'rev_raiting'           => Types::INTEGER,
        'reply_date'            => Types::DATETIME_IMMUTABLE,
        'count_plus'            => Types::INTEGER,
        'rev_status'            => ProductReviewStatus::class,
        'id_review'             => Types::INTEGER,
        'rev_date'              => Types::DATETIME_IMMUTABLE,
        'id_item'               => Types::INTEGER,
        'id_user'               => Types::INTEGER,
    ];

    /**
     * Scope a query to filter by review Id
     *
     * @param QueryBuilder $builder
     * @param int $reviewId
     *
     * @return void
     */
    protected function scopeId(QueryBuilder $builder, int $reviewId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`id_review`",
                $builder->createNamedParameter($reviewId, ParameterType::INTEGER, $this->nameScopeParameter('reviewId'))
            )
        );
    }

    /**
     * Scope a query to filter by review status
     *
     * @param QueryBuilder $builder
     * @param ProductReviewStatus $status
     *
     * @return void
     */
    protected function scopeStatus(QueryBuilder $builder, ProductReviewStatus $status): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`rev_status`",
                $builder->createNamedParameter((string) $status, ParameterType::STRING, $this->nameScopeParameter('reviewStatus'))
            )
        );
    }

    /**
     * Scope a query to filter by has or not a reply
     *
     * @param QueryBuilder $builder
     * @param bool $isReplied
     *
     * @return void
     */
    protected function scopeIsReplied(QueryBuilder $builder, bool $isReplied): void
    {
        if ($isReplied) {
            $builder->andWhere(
                $builder->expr()->isNotNull("`{$this->table}`.`reply`")
            );
        } else {
            $builder->andWhere(
                $builder->expr()->isNull("`{$this->table}`.`reply`")
            );
        }
    }

    /**
     * Scope a query to filter by itemId
     *
     * @param QueryBuilder $builder
     * @param int $itemId
     *
     * @return void
     */
    protected function scopeItemId(QueryBuilder $builder, int $itemId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`id_item`",
                $builder->createNamedParameter($itemId, ParameterType::INTEGER, $this->nameScopeParameter('itemId'))
            )
        );
    }

    /**
     * Scope a query to filter by user Id
     *
     * @param QueryBuilder $builder
     * @param int $userId
     *
     * @return void
     */
    protected function scopeUserId(QueryBuilder $builder, int $userId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`id_user`",
                $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('userId'))
            )
        );
    }

    /**
     * Scope for join with items.
     */
    protected function bindItems(QueryBuilder $builder): void
    {
        /** @var Products_Model $productsModel */
        $productsModel = model(Products_Model::class);
        $productsTable = $productsModel->getTable();
        $reviewsTable = $this->getTable();

        $builder
            ->leftJoin(
                $reviewsTable,
                $productsTable,
                $productsTable,
                "`{$productsTable}`.`id` = `{$reviewsTable}`.`id_item`"
            )
        ;
    }

    /**
     * Resolves static relationships with review item.
     */
    protected function item(): RelationInterface
    {
        return $this->belongsTo(Products_Model::class, 'id_item', 'id');
    }

    /**
     * Resolves static relationships with review images
     */
    protected function images(): RelationInterface
    {
        return $this->hasMany(Product_Reviews_Images_Model::class, 'review_id', 'id_review');
    }
}

// End of file product_reviews_model.php
// Location: /tinymvc/myapp/models/product_reviews_model.php
