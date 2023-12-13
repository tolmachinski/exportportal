<?php

declare(strict_types=1);

use App\Common\Contracts\BuyerIndustries\CollectTypes;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Buyer_Item_Categories_Stats model.
 */
final class Buyer_Item_Categories_Stats_Model extends Model
{
    /**
     * The name of the "created at" column.
     *
     * @var null|string
     */
    protected const CREATED_AT = 'date_added';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'buyer_item_categories_stats';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'BUYER_ITEM_CATEGORIES_STATS';

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
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'idu',
        'id_not_logged',
        self::CREATED_AT,
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'                           => Types::INTEGER,
        'idu'                          => Types::INTEGER,
        'id_not_logged'                => Types::INTEGER,
        'id_category'                  => Types::INTEGER,
        self::CREATED_AT               => Types::DATETIME_IMMUTABLE,
        'type'                         => CollectTypes::class,
    ];

    public function getReportBuyersPerIndustry(array $conditions = []): array
    {
        return array_column(
            $this->findAll(
                [
                    'columns' => [
                        "{$this->qualifyColumn('id_category')}",
                        'COUNT(*) AS countBuyers',
                    ],
                    'joins' => [
                        'users',
                    ],
                    'scopes' => array_merge(
                        [
                            'modelUser'         => 0,
                            'fakeUser'          => 0,
                        ],
                        $conditions
                    ),
                    'group'      => [
                        "{$this->qualifyColumn('id_category')}",
                    ],
                    'order' => [
                        'countBuyers'   => 'DESC',
                    ],
                ]
            ),
            null,
            'id_category'
        );
    }

    /**
     * Gets the buyer's industry of interest.
     *
     * @param int      $idUser - the id of the user
     * @param null|int $limit  - limit of records
     *
     * @return array
     */
    public function getUserRelationIndustries(int $idUser, ?int $limit = null)
    {
        /** @var \Item_Category_Model $itemCategories */
        $itemCategories = model(\Item_Category_Model::class);

        return $this->findAllBy([
            'columns'    => [
                "{$itemCategories->getTable()}.`name`",
                "{$this->getTable()}.*",
            ],
            'scopes' => [
                'id_user'       => $idUser,
            ],
            'joins'      => ['category'],
            'group'      => [
                "{$this->qualifyColumn('id_category')}",
            ],
            'limit' => $limit,
        ]);
    }

    /**
     * Check if record exists for current day.
     *
     * @return bool
     */
    public function existsViewedToday(int $idCategory, CollectTypes $typePage, string $idNotLogged)
    {
        return (bool) $this->countBy([
            'conditions' => [
                'idCategory'  => $idCategory,
                'idNotLogged' => $idNotLogged,
                'statType'    => $typePage,
                'dateAdded'   => new DateTimeImmutable(),
            ],
        ]);
    }

    /**
     * Scope a query by the collect Type.
     */
    protected function scopeStatType(QueryBuilder $builder, CollectTypes $collectType): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('type'),
                $builder->createNamedParameter((string) $collectType, ParameterType::STRING, $this->nameScopeParameter('type'))
            )
        );
    }

    /**
     * Scope a query by the id not logged.
     */
    protected function scopeIdNotLogged(QueryBuilder $builder, string $idNotLogged): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_not_logged'),
                $builder->createNamedParameter($idNotLogged, ParameterType::STRING, $this->nameScopeParameter('idNotLogged'))
            )
        );
    }

    /**
     * Scope a query by the id not logged.
     */
    protected function scopeIdUser(QueryBuilder $builder, int $idUser): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('idu'),
                $builder->createNamedParameter($idUser, ParameterType::INTEGER, $this->nameScopeParameter('idUser'))
            )
        );
    }

    /**
     * Scope a query by the id category.
     */
    protected function scopeIdCategory(QueryBuilder $builder, int $idCategory): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_category'),
                $builder->createNamedParameter($idCategory, ParameterType::INTEGER, $this->nameScopeParameter('idCategory'))
            )
        );
    }

    /**
     * Scope a query by the id category.
     */
    protected function scopeIdCategories(QueryBuilder $builder, array $idCategories): void
    {
        if (empty($idCategories)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                'id_category',
                array_map(
                    fn (int $index, $idCategories) => $builder->createNamedParameter(
                        (string) $idCategories,
                        ParameterType::STRING,
                        $this->nameScopeParameter("{$idCategories}")
                    ),
                    array_keys($idCategories),
                    $idCategories
                )
            )
        );
    }

    /**
     * Scope a query by the null ID USER.
     */
    protected function scopeIduIsNull(QueryBuilder $builder): void
    {
        $builder->andWhere(
            $builder->expr()->isNull($this->qualifyColumn('idu'))
        );
    }

    /**
     * Scope a query by the null ID USER.
     */
    protected function scopeIduIsNotNull(QueryBuilder $builder): void
    {
        $builder->andWhere(
            $builder->expr()->isNotNull($this->qualifyColumn('idu'))
        );
    }

    /**
     * Scope query for specific date added.
     */
    protected function scopeDateAdded(QueryBuilder $builder, DateTimeImmutable $dateAdded): void
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($dateAdded, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "DATE({$this->qualifyColumn('date_added')})",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('dateAdded'))
            )
        );
    }

    /**
     * Get user visited categories
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getUserStatsCategories (int $userId): array
    {

        return array_column($this->findAllBy([
            'columns' => "DISTINCT({$this->qualifyColumn('id_category')})",
            'scopes'    => [
                'id_user' => $userId,
            ],
            'order' => [
                "{$this->qualifyColumn('date_added')}" => "DESC"
            ]
        ]), 'id_category');
    }

    /**
     * Scope for join with category.
     */
    protected function bindCategory(QueryBuilder $builder): void
    {
        /** @var Item_Category_Model $category */
        $category = model(Item_Category_Model::class);

        $builder
            ->leftJoin(
                $this->getTable(),
                $category->getTable(),
                $category->getTable(),
                "`{$category->getTable()}`.`{$category->getPrimaryKey()}` = {$this->qualifyColumn('id_category')}"
            )
        ;
    }

    /**
     * Scope for join with users.
     */
    protected function bindUsers(QueryBuilder $builder): void
    {
        /** @var Users_Model $users */
        $users = model(Users_Model::class);

        $builder
            ->leftJoin(
                $this->getTable(),
                $users->getTable(),
                $users->getTable(),
                "`{$users->getTable()}`.`idu` = {$this->qualifyColumn('idu')}"
            )
        ;
    }

    /**
     * Relation with the category
     *
     * @return RelationInterface
     */
    protected function category(): RelationInterface
    {
        return $this->belongsTo(Item_Category_Model::class, 'id_category', 'category_id')->enableNativeCast();
    }
}

// End of file buyer_item_categories_stats_model.php
// Location: /tinymvc/myapp/models/buyer_item_categories_stats_model.php
