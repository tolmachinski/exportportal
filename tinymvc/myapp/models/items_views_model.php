<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Items_Views model
 */
final class Items_Views_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "items_views";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "ITEMS_VIEWS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'            => Types::INTEGER,
        'item_id'       => Types::INTEGER,
        'user_id'       => Types::INTEGER,
        'viewed_date'   => Types::DATETIME_IMMUTABLE,
    ];

    /**
     * Scope query for sellers IDs - require join with items
     *
     * @param QueryBuilder $builder
     * @param array $sellersIds
     *
     * @return void
     */
    protected function scopeSellersIds(QueryBuilder $builder, array $sellersIds): void
    {
        if (empty($sellersIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->item()->getRelated()->getTable()}`.`id_seller`",
                array_map(
                    fn ($i, $sellerId) => $builder->createNamedParameter((int) $sellerId, ParameterType::INTEGER, $this->nameScopeParameter("sellerId_{$i}")),
                    array_keys($sellersIds),
                    $sellersIds
                )
            )
        );
    }

    /**
     * Scope query for between viewed dates
     *
     * @param QueryBuilder $builder
     * @param DateTimeInterface $viewedDate
     *
     * @return void
     */
    protected function scopeViewedDateTimeGte(QueryBuilder $builder, DateTimeInterface $viewedDate): void
    {
        $builder->andWhere(
            $builder->expr()->gte(
                "`{$this->table}`.`viewed_date`",
                $builder->createNamedParameter($viewedDate->format('Y-m-d H:i:s'), ParameterType::STRING, $this->nameScopeParameter('viewedDateTimeGte'))
            )
        );
    }

    /**
     * Relation with the item
     */
    protected function item(): RelationInterface
    {
        return $this->belongsTo(Products_Model::class, 'item_id')->enableNativeCast();
    }

    /**
     * Scope for join items table.
     */
    protected function bindItems(QueryBuilder $builder): void
    {
        $itemsTable = $this->item()->getRelated()->getTable();

        $builder
            ->leftJoin(
                $this->table,
                $itemsTable,
                $itemsTable,
                "`{$this->table}`.`item_id` = `{$itemsTable}`.`id`"
            )
        ;
    }
}

/* End of file items_views_model.php */
/* Location: /tinymvc/myapp/models/items_views_model.php */
