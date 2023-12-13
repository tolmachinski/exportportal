<?php

declare(strict_types=1);

use App\Common\Contracts\FeaturedProduct\FeaturedStatus;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Database\Types\Types as CustomTypes;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Featured_Products model.
 */
final class Featured_Products_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'item_featured';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'ITEM_FEATURED';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_featured';

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'featured_from_date'    => Types::DATETIME_IMMUTABLE,
        'id_featured'           => Types::INTEGER,
        'create_date'           => Types::DATE_IMMUTABLE,
        'update_date'           => Types::DATETIME_IMMUTABLE,
        'auto_extend'           => Types::INTEGER,
        'end_date'              => Types::DATE_IMMUTABLE,
        'id_item'               => Types::INTEGER,
        'notice'                => CustomTypes::SIMPLE_JSON_ARRAY,
        'extend'                => Types::INTEGER,
        'status'                => FeaturedStatus::class,
        'price'                 => CustomTypes::SIMPLE_MONEY,
        'paid'                  => Types::INTEGER,
    ];

    /**
     * Scope a query by the featured item id.
     */
    protected function scopeId(QueryBuilder $builder, int $featuredItemId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->table}.`id_featured`",
                $builder->createNamedParameter($featuredItemId, ParameterType::INTEGER, $this->nameScopeParameter('featuredItemId'))
            )
        );
    }

    /**
     * Scope a query by the item id.
     */
    protected function scopeItemId(QueryBuilder $builder, int $itemId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->table}.`id_item`",
                $builder->createNamedParameter($itemId, ParameterType::INTEGER, $this->nameScopeParameter('itemId'))
            )
        );
    }

    /**
     * Scope a query by the item ids.
     */
    protected function scopeItemIds(QueryBuilder $builder, array $itemIds): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                "{$this->table}.`id_item`",
                array_map(
                    fn ($index, $itemId) => $builder->createNamedParameter(
                        (int) $itemId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("itemID_{$index}")
                    ),
                    array_keys($itemIds),
                    $itemIds
                )
            )
        );
    }

    /**
     * Scope a query by the item seller id - require join with items table.
     */
    protected function scopeSellerId(QueryBuilder $builder, int $sellerId): void
    {
        /** @var Products_Model $productsModel */
        $productsModel = model(Products_Model::class);

        $builder->andWhere(
            $builder->expr()->eq(
                "{$productsModel->getTable()}.`id_seller`",
                $builder->createNamedParameter($sellerId, ParameterType::INTEGER, $this->nameScopeParameter('sellerId'))
            )
        );
    }

    /**
     * Scope for join with items.
     */
    protected function bindItems(QueryBuilder $builder): void
    {
        /** @var Products_Model $productsModel */
        $productsModel = $this->item()->getRelated();
        $productsTable = $productsModel->getTable();

        $builder
            ->leftJoin(
                $this->table,
                $productsTable,
                $productsTable,
                "`{$productsTable}`.`id` = `{$this->table}`.`id_item`"
            )
        ;
    }

    /**
     * Relation with the product.
     */
    protected function item(): RelationInterface
    {
        return $this->belongsTo(Products_Model::class, 'id_item', 'id');
    }
}

// End of file featured_products_model.php
// Location: /tinymvc/myapp/models/featured_products_model.php
