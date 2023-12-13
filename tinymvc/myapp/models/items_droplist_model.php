<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\ParameterType;
use App\Common\Database\Concerns;
use Doctrine\DBAL\Query\QueryBuilder;
use App\Common\Contracts\Company\CompanyType;
use App\Common\Contracts\ConditionsOperator;
use App\Common\Contracts\Droplist\ItemStatus;
use App\Common\Contracts\Droplist\NotificationType;
use App\Common\Database\Types\Types as CustomTypes;
use App\Common\Database\Relations\RelationInterface;

final class Items_Droplist_Model extends Model
{
    use Concerns\CanSearch;
    /**
     * The name of the "created at" column.
     */
    protected const CREATED_AT = 'created_at';

    /**
     * The name of the "updated at" column.
     */
    protected const UPDATED_AT = 'updated_at';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'items_droplist';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'ITEMS_DROPLIST';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id',
        self::CREATED_AT,
        self::UPDATED_AT,
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'price_changed_at',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'                => Types::INTEGER,
        'item_id'           => Types::INTEGER,
        'user_id'           => Types::INTEGER,
        'company_id'        => Types::INTEGER,
        'item_status'       => ItemStatus::class,
        'item_price'        => CustomTypes::SIMPLE_MONEY,
        'droplist_price'    => CustomTypes::SIMPLE_MONEY,
        'notification_type' => NotificationType::class,
        'price_changed_at'  => Types::DATETIME_IMMUTABLE,
        'created_at'        => Types::DATETIME_IMMUTABLE,
        'updated_at'        => Types::DATETIME_IMMUTABLE,
    ];

    /**
     * Get categories tree.
     */
    public function getItemsCategoriesTree(array $itemsList = []): ?array
    {
        /** @var Categories_Model $categoriesRelation */
        $categoriesRelation = $this->itemsCategories()->getRelated();
        $categoriesRelationTable = $categoriesRelation->getTable();

        /** @var Products_Model $productRelation */
        $productRelation = $this->product()->getRelated();
        $productRelationTable = $productRelation->getTable();

        $query = $this->createQueryBuilder();
        $query->select(
            "`{$categoriesRelationTable}`.`category_id`",
            "`{$categoriesRelationTable}`.`name`",
            "`{$categoriesRelationTable}`.`cat_childrens`",
            "`{$categoriesRelationTable}`.`breadcrumbs`",
            "`{$categoriesRelationTable}`.`parent`",
            "COUNT(`{$categoriesRelationTable}`.`category_id`) as counter",
        );

        $query->from($categoriesRelationTable);

        $query->innerJoin(
            $categoriesRelationTable,
            $productRelationTable,
            $productRelationTable,
            "FIND_IN_SET(`{$productRelationTable}`.`id_cat`, `{$categoriesRelationTable}`.`cat_childrens`) OR `{$productRelationTable}`.`id_cat` = `{$categoriesRelationTable}`.`category_id`"
        );

        $query->where(
            $query->expr()->in(
                "`{$productRelationTable}`.`id`",
                array_map(function ($item) use ($query) {
                    return $query->createNamedParameter((int) $item, ParameterType::INTEGER, $this->nameScopeParameter("itemId{$item}"));
                }, array_column($itemsList, 'item_id'))
            )
        );

        $query->groupBy("`{$categoriesRelationTable}`.`category_id`");

        return $query->execute()->fetchAllAssociative();
    }

    /**
     * Scope by id.
     */
    protected function scopeId(QueryBuilder $builder, int $itemId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`id`",
                $builder->createNamedParameter($itemId, ParameterType::INTEGER, $this->nameScopeParameter('droplistId'))
            )
        );
    }

    /*l
     * Scope by Item id
     */
    protected function scopeItemId(QueryBuilder $builder, int $itemId): void
    {
        $builder
            ->andWhere(
                $builder->expr()->eq(
                    "`{$this->getTable()}`.`item_id`",
                    $builder->createNamedParameter($itemId, ParameterType::INTEGER, $this->nameScopeParameter('itemId'))
                )
            )
        ;
    }

    /**
     * Scope by Item id.
     */
    protected function scopeUserId(QueryBuilder $builder, int $userId): void
    {
        $builder
            ->andWhere(
                $builder->expr()->eq(
                    "`{$this->getTable()}`.`user_id`",
                    $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('userId'))
                )
            )
        ;
    }

    /**
     * Scope status in.
     */
    protected function scopeStatusIn(QueryBuilder $builder, array $statuses = []): void
    {
        if (empty($statuses)) {
            return;
        }

        $builder
            ->andWhere(
                $builder->expr()->in(
                    "`{$this->getTable()}`.`item_status`",
                    array_map(
                        fn (int $i, $status) => $builder->createNamedParameter($status, ParameterType::STRING, $this->nameScopeParameter("itemStatus{$i}")),
                        array_keys($statuses),
                        $statuses
                    )
                )
            )
        ;
    }

    /**
     * Scope status not in.
     */
    protected function scopeStatusNotIn(QueryBuilder $builder, array $statuses = []): void
    {
        if (empty($statuses)) {
            return;
        }

        $builder
            ->andWhere(
                $builder->expr()->notIn(
                    "`{$this->getTable()}`.`item_status`",
                    array_map(
                        fn (int $i, $status) => $builder->createNamedParameter($status, ParameterType::STRING, $this->nameScopeParameter("itemStatus{$i}")),
                        array_keys($statuses),
                        $statuses
                    )
                )
            )
        ;
    }

    /**
     * Scope by category.
     */
    protected function scopeCategory(QueryBuilder $builder, int $categoryId): void
    {
        $productsRelation = $this->product()->getRelated();
        $productsRelationTable = $productsRelation->getTable();

        $categoriesRelation = $this->itemsCategories()->getRelated();

        $builder->leftJoin(
            $this->table,
            $productsRelationTable,
            $productsRelationTable,
            "`{$productsRelationTable}`.`{$productsRelation->getPrimaryKey()}` = `{$this->getTable()}`.`item_id`"
        );

        $subQuery = $this->createQueryBuilder();
        $subQuery->select(
            $categoriesRelation->qualifyColumn('cat_childrens'),
        );
        $subQuery->from('item_category');
        $subQuery->where(
            $builder->expr()->eq(
                $categoriesRelation->qualifyColumn('category_id'),
                $builder->createNamedParameter((int) $categoryId, ParameterType::INTEGER, $this->nameScopeParameter('categoryId'))
            )
        );

        $builder->andWhere(
            $builder->expr()->or(
                "FIND_IN_SET(`{$productsRelationTable}`.`id_cat`, ({$subQuery->getSQL()}))",
                $builder->expr()->eq(
                    "`{$productsRelationTable}`.`id_cat`",
                    $builder->createNamedParameter((int) $categoryId, ParameterType::INTEGER, $this->nameScopeParameter('categoryId'))
                )
            )
        );
    }

    /**
     * Scope by price fluctuation.
     */
    protected function scopePriceFluctuation(QueryBuilder $builder, ConditionsOperator $type): void
    {
        $builder->andWhere(
            $builder->expr()->{$type->value}(
                "`{$this->table}`.`item_price`",
                "`{$this->table}`.`droplist_price`",
            )
        );
    }

    /**
     * Scope by search.
     */
    protected function scopeSearch(QueryBuilder $builder, string $text): void
    {
        $this->appendSearchConditionsToQuery(
            $builder,
            $text,
            ['item_title'],
        );
    }

    /**
     * Relation with the item main iamge.
     */
    protected function sellerCompany(): RelationInterface
    {
        /** @var RelationInterface $relation */
        $relation = $this->belongsTo(Seller_Companies_Model::class, 'company_id', 'id_company');

        $relation->getQuery()->andWhere(
            $relation->getQuery()->expr()->eq(
                $relation->getRelated()->qualifyColumn('type_company'),
                $relation->getQuery()->createNamedParameter((string) CompanyType::COMPANY, ParameterType::STRING, $this->nameScopeParameter('companyType'))
            )
        );

        return $relation;
    }

    /**
     * Relation with user.
     */
    protected function user(): RelationInterface
    {
        return $this->belongsTo(Users_Model::class, 'user_id', 'idu');
    }

    /**
     * Relation with user.
     */
    protected function product(): RelationInterface
    {
        return $this->belongsTo(Products_Model::class, 'item_id', 'id');
    }

    /**
     * Relation with item Photo.
     */
    protected function itemPhotos(): RelationInterface
    {
        return $this->hasMany(Products_Photo_Model::class, 'item_id', 'sale_id');
    }

    /**
     * Relation with item Photo.
     */
    protected function mainPhoto(): RelationInterface
    {
        $photosRelation = $this->itemPhotos()->getRelated();

        $relation = $this->hasOneThrough(
            Products_Photo_Model::class,
            Products_Model::class,
            'id',
            'sale_id',
            'item_id',
            'id',
        );

        $relation->getQuery()->andWhere(
            $relation->getQuery()->expr()->eq(
                $photosRelation->qualifyColumn('main_photo'),
                $relation->getQuery()->createNamedParameter(1, ParameterType::INTEGER, $this->nameScopeParameter('isMainPhoto'))
            )
        );

        return $relation;
    }

    /**
     * Relation for items categores.
     */
    protected function itemsCategories(): RelationInterface
    {
        return $this->hasOneThrough(
            Categories_Model::class,
            Products_Model::class,
            'id',
            'category_id',
            'item_id',
            'id_cat',
        );
    }

    /**
     * Scope by price changed from.
     */
    protected function scopePriceChangedFrom(QueryBuilder $builder, DateTimeInterface $date)
    {
        $builder->andWhere(
            $builder->expr()->gte(
                "DATE(`{$this->table}`.`price_changed_at`)",
                $builder->createNamedParameter($date->format('Y-m-d'), ParameterType::STRING, $this->nameScopeParameter('priceChangedFrom'))
            )
        );
    }

    /**
     * Scope by price changed to.
     */
    protected function scopePriceChangedTo(QueryBuilder $builder, DateTimeInterface $date)
    {
        $builder->andWhere(
            $builder->expr()->lte(
                "DATE(`{$this->table}`.`price_changed_at`)",
                $builder->createNamedParameter($date->format('Y-m-d'), ParameterType::STRING, $this->nameScopeParameter('priceChangedTo'))
            )
        );
    }
}
