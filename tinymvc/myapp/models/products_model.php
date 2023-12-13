<?php

declare(strict_types=1);

use App\Casts\Product\ProductEraCast;
use App\Casts\Product\ProductSizeCast;
use App\Casts\Product\ProductTranslationStateCast;
use App\Casts\Product\ProductVideoSourceCast;
use App\Common\Contracts\Company\CompanyType;
use App\Common\Contracts\Product\ProductDescriptionStatus;
use App\Common\Database\Concerns;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Database\Types\Types as CustomTypes;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Products model.
 */
final class Products_Model extends Model
{
    use Concerns\CanSearch;

    /**
     * The name of the "created at" column.
     */
    protected const CREATED_AT = 'create_date';

    /**
     * The name of the "updated at" column.
     */
    protected const UPDATED_AT = 'update_date';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'items';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'ITEMS';

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
        'id_cat',
        'title',
        'year',
        'price',
        'final_price',
        'weight',
        'item_length',
        'item_width',
        'item_height',
        'moderation_approved_at',
        'moderation_blocked_at',
        'moderation_unblocked_at',
        'moderation_noticed_at',
        'moderation_activity',
        'moderation_activity',
        'moderation_notices',
        'moderation_blocking',
        'draft_expire_date',
        'date_out_of_stock',
        'is_handmade',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'                                 => Types::INTEGER,
        'id_seller'                          => Types::INTEGER,
        'id_cat'                             => Types::INTEGER,
        'year'                               => Types::SMALLINT,
        'era'                                => ProductEraCast::class,
        'price'                              => CustomTypes::SIMPLE_MONEY,
        'discount'                           => Types::INTEGER,
        'final_price'                        => CustomTypes::SIMPLE_MONEY,
        'currency'                           => Types::INTEGER,
        'weight'                             => Types::DECIMAL,
        'item_length'                        => Types::DECIMAL,
        'item_width'                         => Types::DECIMAL,
        'item_height'                        => Types::DECIMAL,
        'size'                               => ProductSizeCast::class,
        'quantity'                           => Types::INTEGER,
        'min_sale_q'                         => Types::INTEGER,
        'max_sale_q'                         => Types::INTEGER,
        'unit_type'                          => Types::INTEGER,
        'create_date'                        => Types::DATETIME_IMMUTABLE,
        'update_date'                        => Types::DATETIME_IMMUTABLE,
        'expire_date'                        => Types::DATETIME_IMMUTABLE,
        'renew'                              => Types::BOOLEAN,
        'p_country'                          => Types::INTEGER,
        'origin_country'                     => Types::INTEGER,
        'state'                              => Types::INTEGER,
        'p_city'                             => Types::INTEGER,
        'video_source'                       => ProductVideoSourceCast::class,
        'status'                             => Types::INTEGER,
        'visible'                            => Types::BOOLEAN,
        'offers'                             => Types::BOOLEAN,
        'order_now'                          => Types::BOOLEAN,
        'samples'                            => Types::BOOLEAN,
        'featured'                           => Types::BOOLEAN,
        'highlight'                          => Types::BOOLEAN,
        'inquiry'                            => Types::BOOLEAN,
        'is_partners_item'                   => Types::BOOLEAN,
        'po'                                 => Types::BOOLEAN,
        'estimate'                           => Types::BOOLEAN,
        'rev_numb'                           => Types::INTEGER,
        'rating'                             => Types::DECIMAL,
        'changed'                            => Types::BOOLEAN,
        'blocked'                            => Types::INTEGER,
        'thumbs_actualized'                  => Types::BOOLEAN,
        'item_categories'                    => Types::SIMPLE_ARRAY,
        'views'                              => Types::INTEGER,
        'total_sold'                         => Types::INTEGER,
        'tags'                               => Types::SIMPLE_ARRAY,
        'has_translation'                    => ProductTranslationStateCast::class,
        'moderation_is_approved'             => Types::BOOLEAN,
        'moderation_approved_at'             => Types::DATETIME_IMMUTABLE,
        'moderation_blocked_at'              => Types::DATETIME_IMMUTABLE,
        'moderation_unblocked_at'            => Types::DATETIME_IMMUTABLE,
        'moderation_noticed_at'              => Types::DATETIME_IMMUTABLE,
        'moderation_activity'                => Types::JSON,
        'moderation_notices'                 => Types::JSON,
        'moderation_blocking'                => Types::JSON,
        'draft'                              => Types::BOOLEAN,
        'draft_expire_date'                  => Types::DATE_IMMUTABLE,
        'out_of_stock_quantity'              => Types::INTEGER,
        'is_out_of_stock'                    => Types::BOOLEAN,
        'date_out_of_stock'                  => Types::DATE_IMMUTABLE,
        'is_distributor'                     => Types::BOOLEAN,
        'has_variants'                       => Types::INTEGER,
        'is_archived'                        => Types::INTEGER,
        'is_handmade'                        => Types::BOOLEAN,
    ];

    /**
     * Updates many records.
     */
    public function updateMany(array $record, array $params = []): int
    {
        // if params contains columns, then it require to contain id item
        $productsToBeUpdated = $this->findAllBy($params);
        $updatingResult = parent::updateMany($record, $params);

        if ($updatingResult) {
            /** @var Elasticsearch_Items_Model $elasticsearchItemsModel */
            $elasticsearchItemsModel = model(Elasticsearch_Items_Model::class);

            // after switching to the latest version of elastic, you will need to rewrite this code for an update
            if (!empty($productsToBeUpdated)) {
                $elasticsearchItemsModel->index(array_column($productsToBeUpdated, 'id'));
            }
        }

        return $updatingResult;
    }

    /**
     * Method for geting the last viewed items for user (either logged or not).
     *
     * @param int|string $userId - the id of the user that is logged or the unique id from cookie for not logged
     * @param int        $itemId - the id of the current viewed item
     * @param bool       $logged - is the user logged or not
     * @param int        $limit  - how many items to return (by default 10)
     */
    public function getItemsForLastViewed($userId, int $itemId, bool $logged = true, int $limit = 10): array
    {
        /** @var Item_Category_Model $itemCategoriesModel */
        $itemCategoriesModel = model(Item_Category_Model::class);
        $itemCategoryTable = $itemCategoriesModel->getTable();

        /** @var Products_Photo_Model $productsPhotoModel */
        $productsPhotoModel = model(Products_Photo_Model::class);
        $productsPhotoTable = $productsPhotoModel->getTable();

        /** @var Last_Viewed_Items_Model $lastViewedItemsModel */
        $lastViewedItemsModel = model(Last_Viewed_Items_Model::class);
        $lastViewedItemsTable = $lastViewedItemsModel->getTable();

        /** @var Countries_Model $countriesModel */
        $countriesModel = model(Countries_Model::class);
        $countriesTable = $countriesModel->getTable();

        $itemsTable = $this->getTable();

        $queryBuilder = $this->createQueryBuilder();
        $queryBuilder
            ->select([
                "`{$itemsTable}`.`id`",
                "`{$itemsTable}`.`title`",
                "`{$itemsTable}`.`price`",
                "`{$itemsTable}`.`final_price`",
                "`{$itemsTable}`.`discount`",
                "`{$itemsTable}`.`rating`",
                "`{$itemsTable}`.`rev_numb`",
                "`{$itemsTable}`.`views`",
                "`{$itemsTable}`.`has_variants`",
                "`{$productsPhotoTable}`.`photo_name`",
                "`{$itemCategoryTable}`.`p_or_m`",
                "`{$lastViewedItemsTable}`.`date_updated`",
                "`{$countriesTable}`.`country` as country_name",
            ])
            ->from($itemsTable)
            ->innerJoin(
                $itemsTable,
                $itemCategoryTable,
                $itemCategoryTable,
                "`{$itemsTable}`.`id_cat` = `{$itemCategoryTable}`.`category_id`"
            )
            ->leftJoin(
                $itemsTable,
                $productsPhotoTable,
                $productsPhotoTable,
                "`{$itemsTable}`.`id` = `{$productsPhotoTable}`.`sale_id` AND `{$productsPhotoTable}`.`main_photo` = 1"
            )
            ->leftJoin(
                $itemsTable,
                $countriesTable,
                $countriesTable,
                "`{$itemsTable}`.`p_country` = `{$countriesTable}`.`id`"
            )
            ->leftJoin(
                $itemsTable,
                $lastViewedItemsTable,
                $lastViewedItemsTable,
                "`{$itemsTable}`.`id` = `{$lastViewedItemsTable}`.`id_item`"
            )
            ->andWhere(
                $queryBuilder->expr()->neq(
                    "`{$lastViewedItemsTable}`.`id_item`",
                    $queryBuilder->createNamedParameter($itemId, ParameterType::INTEGER, $this->nameScopeParameter('itemId')),
                )
            )
            ->andWhere(
                $queryBuilder->expr()->eq(
                    $logged ? "`{$lastViewedItemsTable}`.`id_user`" : "`{$lastViewedItemsTable}`.`id_not_logged`",
                    $logged
                        ? $queryBuilder->createNamedParameter((int) $userId, ParameterType::INTEGER, $this->nameScopeParameter('userId'))
                        : $queryBuilder->createNamedParameter((string) $userId, ParameterType::STRING, $this->nameScopeParameter('notLoggedId'))
                )
            )
            ->addOrderBy("`{$lastViewedItemsTable}`.`date_updated`", 'DESC')
            ->setMaxResults((int) $limit)
        ;

        return $this->restoreAttributesList(
            array_column(
                $queryBuilder->execute()->fetchAllAssociative(),
                null,
                'id'
            )
        );
    }

    /**
     * Get items for ElasticSearch.
     *
     * @param array|?int $id
     *
     * @return array
     */
    public function getItemsForElastic($id = null): ?array
    {
        $queryBuilder = $this->createQueryBuilder();

        /** @var Users_Model $usersModel */
        $usersModel = model(Users_Model::class);
        $usersModelTable = $usersModel->getTable();

        /** @var Item_Category_Model $itemCategoryModel */
        $itemCategoryModel = model(Item_Category_Model::class);
        $itemCategoryModelTable = $itemCategoryModel->getTable();

        /** @var Seller_Companies_Model $companyModel */
        $companyModel = model(Seller_Companies_Model::class);
        $companyModelTable = $companyModel->getTable();

        /** @var Countries_Model $countriesModel */
        $countriesModel = model(Countries_Model::class);
        $countriesModelTable = $countriesModel->getTable();

        $itemsAttrSelectQueryBuilder = $this->createQueryBuilder();
        $itemsAttrSelectQueryBuilder
            ->select("GROUP_CONCAT(CONCAT('attr_', `ia`.`attr`, '_value_', `ia`.`attr_value`) SEPARATOR ',')")
            ->from('item_attr', 'ia')
            ->innerJoin('ia', 'item_cat_attr', 'ica', '`ia`.`attr` = `ica`.`id`')
            ->andWhere(
                $queryBuilder->expr()->in(
                    '`ica`.`attr_type`',
                    [
                        $queryBuilder->createNamedParameter('select', ParameterType::STRING, $this->nameScopeParameter('select')),
                        $queryBuilder->createNamedParameter('multiselect', ParameterType::STRING, $this->nameScopeParameter('multiselect')),
                    ]
                ),
                $queryBuilder->expr()->eq(
                    '`ia`.`item`',
                    $this->qualifyColumn('id'),
                )
            )
            ->groupBy($this->qualifyColumn('`ia`.`item`'))
        ;

        $itemsAttrRangeQueryBuilder = $this->createQueryBuilder();
        $itemsAttrRangeQueryBuilder
            ->select("GROUP_CONCAT(CONCAT('attr_range_', `ia`.`attr`, '-', REPLACE(`ia`.`attr_value`, '|~%~|', ' ')) SEPARATOR '|~%~|')")
            ->from('item_attr', 'ia')
            ->innerJoin('ia', 'item_cat_attr', 'ica', "`ia`.`attr` = `ica`.`id` AND `ica`.`attr_type` = 'range'")
            ->where(
                $queryBuilder->expr()->eq(
                    '`ia`.`item`',
                    $queryBuilder->createNamedParameter($this->qualifyColumn('id'), ParameterType::INTEGER, $this->nameScopeParameter('id')),
                )
            )
        ;

        $itemsAttrTextQueryBuilder = $this->createQueryBuilder();
        $itemsAttrTextQueryBuilder
            ->select("GROUP_CONCAT(CONCAT('attr_text_', `ia`.`attr`, '-', REPLACE(`ia`.`attr_value`, '|~%~|', ' ')) SEPARATOR '|~%~|')")
            ->from('item_attr', 'ia')
            ->innerJoin('ia', 'item_cat_attr', 'ica', "`ia`.`attr` = `ica`.`id` AND `ica`.`attr_type` = 'text'")
            ->where(
                $queryBuilder->expr()->eq(
                    '`ia`.`item`',
                    $queryBuilder->createNamedParameter($this->qualifyColumn('id'), ParameterType::INTEGER, $this->nameScopeParameter('id')),
                )
            )
        ;

        $itemCategoriesNamesQueryBuilder = $this->createQueryBuilder();
        $itemCategoriesNamesQueryBuilder
            ->select("GROUP_CONCAT(name SEPARATOR '|')")
            ->from('item_category', 'ic')
            ->where("FIND_IN_SET(category_id, {$this->qualifyColumn('item_categories')})")
        ;

        $queryBuilder->select(
            $this->qualifyColumn('id'),
            $this->qualifyColumn('id_seller'),
            $this->qualifyColumn('id_cat'),
            $this->qualifyColumn('title'),
            $this->qualifyColumn('year'),
            $this->qualifyColumn('price'),
            $this->qualifyColumn('discount'),
            $this->qualifyColumn('final_price'),
            $this->qualifyColumn('currency'),
            $this->qualifyColumn('weight'),
            $this->qualifyColumn('item_length'),
            $this->qualifyColumn('item_width'),
            $this->qualifyColumn('item_height'),
            $this->qualifyColumn('quantity'),
            $this->qualifyColumn('min_sale_q'),
            $this->qualifyColumn('unit_type'),
            $this->qualifyColumn('create_date'),
            $this->qualifyColumn('update_date'),
            $this->qualifyColumn('expire_date'),
            $this->qualifyColumn('p_country'),
            $this->qualifyColumn('p_city'),
            $this->qualifyColumn('item_categories'),
            $this->qualifyColumn('state'),
            $this->qualifyColumn('description'),
            $this->qualifyColumn('search_info'),
            $this->qualifyColumn('offers'),
            $this->qualifyColumn('samples'),
            $this->qualifyColumn('order_now'),
            $this->qualifyColumn('featured'),
            $this->qualifyColumn('highlight'),
            $this->qualifyColumn('rating'),
            $this->qualifyColumn('rev_numb'),
            $this->qualifyColumn('views'),
            $this->qualifyColumn('total_sold'),
            $this->qualifyColumn('tags'),
            $this->qualifyColumn('is_out_of_stock'),
            $this->qualifyColumn('has_variants'),
            $this->qualifyColumn('is_handmade'),
            $this->qualifyColumn('origin_country'),
            '`cb`.`id_company`',
            '`cb`.`name_company` AS `company_info`',
            '`cb`.`accreditation`',
            '`ic`.`p_or_m`',
            '`ic`.`is_restricted`',
            '`ic`.`industry_id` AS `industryId`',
            '`ip`.`photo_name`',
            '`ip`.`photo_thumbs`',
            '`item_featured`.`featured_from_date`',
            '`pc`.`country` AS `country_name`',
            '`pco`.`country` AS `origin_country_name`',
            "({$itemsAttrSelectQueryBuilder->getSQL()}) AS `item_attr_select`",
            "({$itemsAttrRangeQueryBuilder->getSQL()}) AS `item_attr_range`",
            "({$itemsAttrTextQueryBuilder->getSQL()}) AS `item_attr_text`",
            "({$itemCategoriesNamesQueryBuilder->getSQL()}) AS `item_categories_names`"
        );

        $queryBuilder->from($this->getTable());

        $queryBuilder->leftJoin(
            $this->getTable(),
            $usersModelTable,
            $usersModelTable,
            "{$this->qualifyColumn('id_seller')} = `{$usersModelTable}`.`idu`"
        );

        $queryBuilder->leftJoin(
            $this->getTable(),
            $companyModelTable,
            'cb',
            "cb.id_user = {$this->qualifyColumn('id_seller')} AND `cb`.`parent_company` = 0"
        );

        $queryBuilder->leftJoin(
            $this->getTable(),
            $itemCategoryModelTable,
            'ic',
            "ic.category_id = {$this->qualifyColumn('id_cat')}"
        );

        $queryBuilder->leftJoin(
            $this->getTable(),
            'item_photo',
            'ip',
            "{$this->qualifyColumn('id')} = `ip`.`sale_id` AND `ip`.`main_photo` = 1"
        );

        $queryBuilder->leftJoin(
            $this->getTable(),
            $countriesModelTable,
            'pc',
            "{$this->qualifyColumn('p_country')} = `pc`.`id`"
        );

        $queryBuilder->leftJoin(
            $this->getTable(),
            $countriesModelTable,
            'pco',
            "{$this->qualifyColumn('origin_country')} = `pco`.`id`"
        );

        $queryBuilder->leftJoin(
            $this->getTable(),
            'item_featured',
            'item_featured',
            "{$this->qualifyColumn('id')} = `item_featured`.`id_item`"
        );

        $queryBuilder->andWhere(
            $queryBuilder->expr()->eq(
                $this->qualifyColumn('visible'),
                $queryBuilder->createNamedParameter(1, ParameterType::INTEGER, $this->nameScopeParameter('visible'))
            ),
            $queryBuilder->expr()->eq(
                $this->qualifyColumn('moderation_is_approved'),
                $queryBuilder->createNamedParameter(1, ParameterType::INTEGER, $this->nameScopeParameter('moderation_is_approved'))
            ),
            $queryBuilder->expr()->eq(
                $this->qualifyColumn('blocked'),
                $queryBuilder->createNamedParameter(0, ParameterType::INTEGER, $this->nameScopeParameter('blocked'))
            ),
            $queryBuilder->expr()->in(
                $this->qualifyColumn('status'),
                [1, 2, 3]
            ),
            $queryBuilder->expr()->eq(
                $this->qualifyColumn('draft'),
                $queryBuilder->createNamedParameter(0, ParameterType::INTEGER, $this->nameScopeParameter('draft'))
            ),
            $queryBuilder->expr()->eq(
                "`{$usersModelTable}`.`fake_user`",
                $queryBuilder->createNamedParameter(0, ParameterType::INTEGER, $this->nameScopeParameter('fake_user'))
            ),
            $queryBuilder->expr()->eq(
                "`{$usersModelTable}`.`status`",
                $queryBuilder->createNamedParameter('active', ParameterType::STRING, $this->nameScopeParameter('status'))
            )
        );

        // Search params
        if (is_array($id) && !empty($id)) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->in(
                    $this->qualifyColumn('id'),
                    array_map(function ($item) use ($queryBuilder) {
                        return $queryBuilder->createNamedParameter((int) $item, ParameterType::INTEGER, $this->nameScopeParameter('id'));
                    }, (array) $id)
                )
            );
        } elseif (is_numeric($id) && $id > 0) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    $this->qualifyColumn('id'),
                    $queryBuilder->createNamedParameter((int) $id, ParameterType::INTEGER, $this->nameScopeParameter('id'))
                )
            );
        }

        $queryBuilder->groupBy($this->qualifyColumn('id'));

        return $queryBuilder->execute()->fetchAllAssociative();
    }

    /**
     * Undocumented function.
     */
    public function getUserAttrs(int $itemId): array
    {
        $queryBuilder = $this->createQueryBuilder();

        $queryBuilder->select(['*']);

        $queryBuilder->from('item_user_attr');

        $queryBuilder->where(
            $queryBuilder->expr()->eq(
                'id_item',
                $queryBuilder->createNamedParameter((int) $itemId, ParameterType::INTEGER, $this->nameScopeParameter('itemId'))
            )
        );

        $queryBuilder->addOrderBy('id', 'DESC');

        return $queryBuilder->execute()->fetchAllAssociative();
    }

    /**
     * Scope by item id.
     */
    protected function scopeId(QueryBuilder $builder, int $itemId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`id`",
                $builder->createNamedParameter($itemId, ParameterType::INTEGER, $this->nameScopeParameter('itemId'))
            )
        );
    }

    /**
     * Scope query by items ids.
     */
    protected function scopeIds(QueryBuilder $builder, array $itemIds): void
    {
        if (empty($itemIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->getTable()}`.`id`",
                array_map(
                    fn (int $i, $itemId) => $builder->createNamedParameter((int) $itemId, ParameterType::INTEGER, $this->nameScopeParameter("itemId_{$i}")),
                    array_keys($itemIds),
                    $itemIds
                )
            )
        );
    }

    /**
     * Scope a query by the seller id.
     */
    protected function scopeSellerId(QueryBuilder $builder, int $sellerId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`id_seller`",
                $builder->createNamedParameter($sellerId, ParameterType::INTEGER, $this->nameScopeParameter('sellerId'))
            )
        );
    }

    /**
     * Scope query by sellers ids.
     */
    protected function scopeSellersIds(QueryBuilder $builder, array $sellerIds): void
    {
        if (empty($sellerIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->getTable()}`.`id_seller`",
                array_map(
                    fn (int $i, $sellerId) => $builder->createNamedParameter((int) $sellerId, ParameterType::INTEGER, $this->nameScopeParameter("sellerId_{$i}")),
                    array_keys($sellerIds),
                    $sellerIds
                )
            )
        );
    }

    /**
     * Scope a query to filter by text search.
     */
    protected function scopeSearch(QueryBuilder $builder, string $text): void
    {
        $this->appendSearchConditionsToQuery(
            $builder,
            $text,
            ['title', 'description', 'search_info'],
            ['title', 'description', 'search_info'],
        );
    }

    /**
     * Scope by keywords.
     *
     * @deprecated - This is only copy of scope from items_model::get_items
     */
    protected function scopeKeywords(QueryBuilder $builder, string $keywords): void
    {
        $builder->andWhere(
            sprintf(
                "MATCH (`{$this->table}`.`title`, `{$this->table}`.`description`, `{$this->table}`.`search_info`) AGAINST (%s)",
                $builder->createNamedParameter($keywords, ParameterType::STRING, $this->nameScopeParameter('searchKeywords'))
            )
        );
    }

    /**
     * Scope visibility.
     */
    protected function scopeVisible(QueryBuilder $builder, int $visible)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.visible",
                $builder->createNamedParameter((int) $visible, ParameterType::INTEGER, $this->nameScopeParameter('visible'))
            )
        );
    }

    /**
     * Scope moderation is approved.
     */
    protected function scopeModeration(QueryBuilder $builder, int $moderation)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.moderation_is_approved",
                $builder->createNamedParameter((int) $moderation, ParameterType::INTEGER, $this->nameScopeParameter('moderation_is_approved'))
            )
        );
    }

    /**
     * Scope a query by the blocking column value.
     */
    protected function scopeBlockedValue(QueryBuilder $builder, int $blockedValue): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`blocked`",
                $builder->createNamedParameter($blockedValue, ParameterType::INTEGER, $this->nameScopeParameter('blockedValue'))
            )
        );
    }

    /**
     * Scope by blocked status.
     */
    protected function scopeIsBlocked(QueryBuilder $builder, int $isBlocked): void
    {
        $sign = 0 == $isBlocked ? '=' : '>';
        $builder->andWhere("`{$this->table}`.`blocked` {$sign} 0");
    }

    /**
     * Scope draft.
     */
    protected function scopeDraft(QueryBuilder $builder, int $draft)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.draft",
                $builder->createNamedParameter((int) $draft, ParameterType::INTEGER, $this->nameScopeParameter('draft'))
            )
        );
    }

    /**
     * Scope items by archived status.
     */
    protected function scopearchived(QueryBuilder $builder, int $isArchived): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->table}.is_archived",
                $builder->createNamedParameter($isArchived, ParameterType::INTEGER, $this->nameScopeParameter('isArchived'))
            )
        );
    }

    /**
     * Scope by featured items.
     */
    protected function scopeFeatured(QueryBuilder $builder, int $isFeatured): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`featured`",
                $builder->createNamedParameter($isFeatured, ParameterType::INTEGER, $this->nameScopeParameter('isFeatured'))
            )
        );
    }

    /**
     * Scope by highlighted items.
     */
    protected function scopeHighlighted(QueryBuilder $builder, int $isHighlighted): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`highlight`",
                $builder->createNamedParameter($isHighlighted, ParameterType::INTEGER, $this->nameScopeParameter('isHighlighted'))
            )
        );
    }

    /**
     * Scope by partnered items.
     */
    protected function scopePartneredItem(QueryBuilder $builder, int $isPartnered): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`is_partners_item`",
                $builder->createNamedParameter($isPartnered, ParameterType::INTEGER, $this->nameScopeParameter('isPartnered'))
            )
        );
    }

    /**
     * Scope by category ids.
     */
    protected function scopeCategoryIds(QueryBuilder $builder, array $categoryIds): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->getTable()}`.`id_cat`",
                array_map(
                    fn ($index, $categoryId) => $builder->createNamedParameter(
                        (int) $categoryId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("categoryId_{$index}")
                    ),
                    array_keys($categoryIds),
                    $categoryIds
                )
            )
        );
    }

    /**
     * Scope by country id.
     */
    protected function scopeCountryId(QueryBuilder $builder, int $countryId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`p_country`",
                $builder->createNamedParameter($countryId, ParameterType::INTEGER, $this->nameScopeParameter('countryId'))
            )
        );
    }

    /**
     * Scope by country id.
     */
    protected function scopeCityId(QueryBuilder $builder, int $cityId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`p_city`",
                $builder->createNamedParameter($cityId, ParameterType::INTEGER, $this->nameScopeParameter('cityId'))
            )
        );
    }

    /**
     * Scope by country id - for using it need bindSeller.
     */
    protected function scopeFakeUser(QueryBuilder $builder, int $fakeUser): void
    {
        /** @var Users_Model $usersModel */
        $usersModel = model(Users_Model::class);
        $usersTable = $usersModel->getTable();

        $builder->andWhere(
            $builder->expr()->eq(
                "`{$usersTable}`.`fake_user`",
                $builder->createNamedParameter($fakeUser, ParameterType::INTEGER, $this->nameScopeParameter('fakeUser'))
            )
        );
    }

    /**
     * Scope by items created from date gte.
     *
     * @param string $createDate - format Y-m-d
     */
    protected function scopeCreatedFromDateGte(QueryBuilder $builder, string $createDate): void
    {
        $builder->andWhere(
            $builder->expr()->gte(
                "DATE(`{$this->table}`.`create_date`)",
                $builder->createNamedParameter($createDate, ParameterType::STRING, $this->nameScopeParameter('createdFromDateGte'))
            )
        );
    }

    /**
     * Scope by items created from date lte.
     *
     * @param string $createDate - format Y-m-d
     */
    protected function scopeCreatedFromDateLte(QueryBuilder $builder, string $createDate): void
    {
        $builder->andWhere(
            $builder->expr()->lte(
                "DATE(`{$this->table}`.`create_date`)",
                $builder->createNamedParameter($createDate, ParameterType::STRING, $this->nameScopeParameter('createdFromDateLte'))
            )
        );
    }

    /**
     * Scope by items expire from date gte.
     *
     * @param string $expireDate - format Y-m-d
     */
    protected function scopeExpireFromDateGte(QueryBuilder $builder, string $expireDate): void
    {
        $builder->andWhere(
            $builder->expr()->gte(
                "DATE(`{$this->table}`.`expire_date`)",
                $builder->createNamedParameter($expireDate, ParameterType::STRING, $this->nameScopeParameter('expireFromDateGte'))
            )
        );
    }

    /**
     * Scope by items expire from date lte.
     */
    protected function scopeExpireFromDateLte(QueryBuilder $builder, string $expireDate): void
    {
        $builder->andWhere(
            $builder->expr()->lte(
                "DATE(`{$this->table}`.`expire_date`)",
                $builder->createNamedParameter($expireDate, ParameterType::STRING, $this->nameScopeParameter('expireFromDateLte'))
            )
        );
    }

    /**
     * Scope by items draft expire date.
     *
     * @param string $date - format Y-m-d
     */
    protected function scopeDraftExpireDate(QueryBuilder $builder, string $date): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "DATE(`{$this->table}`.`draft_expire_date`)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('draftExpireDate'))
            )
        );
    }

    /**
     * Scope by items updated from date gte.
     *
     * @param string $updateDate - format Y-m-d
     */
    protected function scopeUpdatedFromDateGte(QueryBuilder $builder, string $updateDate): void
    {
        $builder->andWhere(
            $builder->expr()->gte(
                "DATE(`{$this->table}`.`update_date`)",
                $builder->createNamedParameter($updateDate, ParameterType::STRING, $this->nameScopeParameter('updatedFromDateGte'))
            )
        );
    }

    /**
     * Scope by items updated from date lte.
     *
     * @param string $updateDate - format Y-m-d
     */
    protected function scopeUpdatedFromDateLte(QueryBuilder $builder, string $updateDate): void
    {
        $builder->andWhere(
            $builder->expr()->lte(
                "DATE(`{$this->table}`.`update_date`)",
                $builder->createNamedParameter($updateDate, ParameterType::STRING, $this->nameScopeParameter('updatedFromDateLte'))
            )
        );
    }

    /**
     * Scope by description translation status - for using it need bindDescriptions.
     */
    protected function scopeDescriptionStatus(QueryBuilder $builder, ProductDescriptionStatus $translationStatus): void
    {
        /** @var Product_Descriptions_Model $productDescriptionsModel */
        $productDescriptionsModel = model(Product_Descriptions_Model::class);

        $builder->andWhere(
            $builder->expr()->eq(
                "`{$productDescriptionsModel->getTable()}`.`status`",
                $builder->createNamedParameter((string) $translationStatus, ParameterType::STRING, $this->nameScopeParameter('descriptionTranslationStatus'))
            )
        );
    }

    /**
     * Scope by user name or email - for using it need bindSeller.
     *
     * @deprecated - This is only copy of scope from items_model::get_items
     */
    protected function scopeSearchByNameOrEmail(QueryBuilder $builder, string $nameOrEmail): void
    {
        /** @var Users_Model $usersModel */
        $usersModel = model(Users_Model::class);
        $usersTable = $usersModel->getTable();

        $builder->andWhere(
            $builder->expr()->or(
                $builder->expr()->like(
                    "`{$usersTable}`.`fname`",
                    $builder->createNamedParameter("%{$nameOrEmail}%", ParameterType::STRING, $this->nameScopeParameter('likeUserFname'))
                ),
                $builder->expr()->like(
                    "`{$usersTable}`.`lname`",
                    $builder->createNamedParameter("%{$nameOrEmail}%", ParameterType::STRING, $this->nameScopeParameter('likeUserLname'))
                ),
                $builder->expr()->like(
                    "`{$usersTable}`.`email`",
                    $builder->createNamedParameter($nameOrEmail, ParameterType::STRING, $this->nameScopeParameter('likeUserEmail'))
                ),
            )
        );
    }

    /**
     * Scope by seller company name - for using it need bindSellerCompany.
     *
     * @deprecated - This is only copy of scope from items_model::get_items
     */
    protected function scopeSearchByCompanyName(QueryBuilder $builder, string $companyName): void
    {
        /** @var Seller_Companies_Model $sellerCompaniesModel */
        $sellerCompaniesModel = model(Seller_Companies_Model::class);
        $sellerCompaniesTable = $sellerCompaniesModel->getTable();

        $builder->andWhere(
            $builder->expr()->or(
                $builder->expr()->like(
                    "`{$sellerCompaniesTable}`.`name_company`",
                    $builder->createNamedParameter("%{$companyName}%", ParameterType::STRING, $this->nameScopeParameter('likeCompanyName'))
                ),
                $builder->expr()->like(
                    "`{$sellerCompaniesTable}`.`legal_name_company`",
                    $builder->createNamedParameter("%{$companyName}%", ParameterType::STRING, $this->nameScopeParameter('likeCompanyLegalName'))
                ),
            )
        );
    }

    /**
     * Relation with the seller.
     */
    protected function seller(): RelationInterface
    {
        return $this->belongsTo(Users_Model::class, 'id_seller')->enableNativeCast();
    }

    /**
     * Relation with the product unit type.
     */
    protected function productUnitType(): RelationInterface
    {
        return $this->belongsTo(Unit_Types_Model::class, 'unit_type')->enableNativeCast();
    }

    /**
     * Relation with the product currency.
     */
    protected function productCurrency(): RelationInterface
    {
        return $this->belongsTo(Currencies_Model::class, 'currency')->enableNativeCast();
    }

    /**
     * Relation with the product country.
     */
    protected function productCountry(): RelationInterface
    {
        return $this->belongsTo(Countries_Model::class, 'p_country')->enableNativeCast();
    }

    /**
     * Relation with the product state.
     */
    protected function productState(): RelationInterface
    {
        return $this->belongsTo(States_Model::class, 'state')->enableNativeCast();
    }

    /**
     * Relation with the product city.
     */
    protected function productCity(): RelationInterface
    {
        return $this->belongsTo(Cities_Model::class, 'p_city')->enableNativeCast();
    }

    /**
     * Relation with the product category.
     */
    protected function category(): RelationInterface
    {
        /** @var Categories_Model $categoriesModel */
        $categoriesModel = model(Categories_Model::class);
        $categoriesTable = $categoriesModel->getTable();

        /** @var RelationInterface $relation */
        $relation = $this->belongsTo(Categories_Model::class, 'id_cat', 'category_id')->enableNativeCast();

        $relation
            ->getQuery()
            ->select(
                "`{$categoriesTable}`.*",
                '`industryTable`.`name` AS industryName'
            )
            ->leftJoin(
                $categoriesTable,
                $categoriesTable,
                'industryTable',
                "`{$categoriesTable}`.`industry_id` = `industryTable`.`category_id`",
                'left'
            )
        ;

        return $relation;
    }

    /**
     * Relation with the item main iamge.
     */
    protected function mainPhoto(): RelationInterface
    {
        /** @var RelationInterface $relation */
        $relation = $this->hasOne(Products_Photo_Model::class, 'sale_id', 'id')->enableNativeCast();
        $relation
            ->getQuery()
            ->andWhere("{$relation->getRelated()->qualifyColumn('main_photo')} = 1")
        ;

        return $relation;
    }

    /**
     * Relation with the item main iamge.
     */
    protected function sellerCompany(): RelationInterface
    {
        /** @var RelationInterface $relation */
        $relation = $this->belongsTo(Seller_Companies_Model::class, 'id_seller', 'id_user');

        $relation->getQuery()->andWhere(
            $relation->getQuery()->expr()->eq(
                $relation->getRelated()->qualifyColumn('type_company'),
                $relation->getQuery()->createNamedParameter((string) CompanyType::COMPANY, ParameterType::STRING, $this->nameScopeParameter('companyType'))
            )
        );

        return $relation;
    }

    /**
     * Scope for join with items_descriptions table.
     */
    protected function bindDescriptions(QueryBuilder $builder): void
    {
        /** @var Product_Descriptions_Model $productDescriptionsModel */
        $productDescriptionsModel = model(Product_Descriptions_Model::class);
        $productDescriptionsTable = $productDescriptionsModel->getTable();

        $builder
            ->leftJoin(
                $this->table,
                $productDescriptionsTable,
                $productDescriptionsTable,
                "`{$productDescriptionsTable}`.`id_item` = `{$this->table}`.`id`"
            )
        ;
    }

    /**
     * Scope for join with users table.
     */
    protected function bindSeller(QueryBuilder $builder): void
    {
        /** @var Users_Model $usersModel */
        $usersModel = model(Users_Model::class);
        $usersTable = $usersModel->getTable();

        $builder
            ->leftJoin(
                $this->table,
                $usersTable,
                $usersTable,
                "`{$usersTable}`.`idu` = `{$this->table}`.`id_seller`"
            )
        ;
    }

    /**
     * Scope for join with company_base table.
     */
    protected function bindSellerCompany(QueryBuilder $builder): void
    {
        /** @var Seller_Companies_Model $sellerCompaniesModel */
        $sellerCompaniesModel = model(Seller_Companies_Model::class);
        $sellerCompaniesTable = $sellerCompaniesModel->getTable();
        $companyType = (string) CompanyType::COMPANY;

        $builder
            ->leftJoin(
                $this->table,
                $sellerCompaniesTable,
                $sellerCompaniesTable,
                "`{$sellerCompaniesTable}`.`id_user` = `{$this->table}`.`id_seller` AND `{$sellerCompaniesTable}`.`type_company` = '{$companyType}'"
            )
        ;
    }
}

// End of file products_model.php
// Location: /tinymvc/myapp/models/products_model.php
