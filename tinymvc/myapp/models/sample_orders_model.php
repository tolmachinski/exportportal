<?php

declare(strict_types=1);

use App\Common\Database\Concerns;
use App\Common\Database\Model;
use App\Common\Database\PortableModel;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Database\Types\Types as CustomTypes;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;
use Money\Money;
use Symfony\Component\String\UnicodeString;

/**
 * Model for sample orders.
 */
class Sample_Orders_Model extends Model
{
    use Concerns\CanSearch;
    use Concerns\ConvertsAttributes;

    /**
     * The ID of the bill type used for sample orders;.
     */
    public const ORDER_BILL_TYPE = 7;

    /**
     * {@inheritdoc}
     */
    protected const CREATED_AT = 'creation_date';

    /**
     * {@inheritdoc}
     */
    protected const UPDATED_AT = 'update_date';

    /**
     * {@inheritdoc}
     */
    protected string $table = 'sample_orders';

    private $table_alias = 'SAMPLE';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';

    /**
     * {@inheritdoc}
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected int $perPage = 10;

    /**
     * {@inheritdoc}
     */
    protected array $guarded = [
        'id',
        self::CREATED_AT,
        self::UPDATED_AT,
    ];

    /**
     * {@inheritdoc}
     */
    protected array $casts = [
        'id'                       => Types::INTEGER,
        'id_buyer'                 => Types::INTEGER,
        'id_seller'                => Types::INTEGER,
        'id_shipper'               => Types::INTEGER,
        'id_invoice'               => Types::INTEGER,
        'id_status'                => Types::INTEGER,
        'id_theme'                 => Types::INTEGER,
        'price'                    => CustomTypes::SIMPLE_MONEY,
        'final_price'              => CustomTypes::SIMPLE_MONEY,
        'discount'                 => Types::DECIMAL,
        'weight'                   => Types::DECIMAL,
        'purchase_order'           => Types::JSON,
        'purchase_order_timeline'  => Types::JSON,
        'purchased_products'       => Types::JSON,
        'package_detail'           => Types::JSON,
        'external_bills'           => Types::JSON,
        'ship_from_country'        => Types::INTEGER,
        'ship_from_state'          => Types::INTEGER,
        'ship_from_city'           => Types::INTEGER,
        'ship_to_country'          => Types::INTEGER,
        'ship_to_state'            => Types::INTEGER,
        'ship_to_city'             => Types::INTEGER,
        'creation_date'            => Types::DATETIME_IMMUTABLE,
        'update_date'              => Types::DATETIME_IMMUTABLE,
        'cancelation_date'         => Types::DATETIME_MUTABLE,
        'dispute_date'             => Types::DATETIME_MUTABLE,
        'delivery_date'            => Types::DATE_MUTABLE,
        'pickup_date'              => Types::DATE_MUTABLE,
        'cancel_request'           => Types::BOOLEAN,
        'dispute_opened'           => Types::BOOLEAN,
    ];

    /**
     * {@inheritdoc}
     */
    protected array $nullable = [
        'description',
        'ship_from',
        'ship_from_country',
        'ship_from_state',
        'ship_from_city',
        'ship_from_zip',
        'ship_from_address',
        'ship_to',
        'ship_to_country',
        'ship_to_state',
        'ship_to_city',
        'ship_to_zip',
        'ship_to_address',
    ];

    public function get_sample($orderId, array $params = array())
    {
        return $this->findRecord(
            null,
            $this->table,
            null,
            $this->getPrimaryKey(),
            $orderId,
            $params
        );
    }

    /**
     * Checks if record is accessible for the user.
     */
    final public function is_accessible_for(int $id, int $user_id): bool
    {
        $counter = $this->findRecord(
            null,
            $this->getTable(),
            null,
            $this->getPrimaryKey(),
            $id,
            [
                'columns'    => ['COUNT(*) AS `AGGREGATE`'],
                'conditions' => ['owned_by' => $user_id],
            ]
        );

        return (bool) (int) ($counter['AGGREGATE'] ?? 0);
    }

    /**
     * Paginates over samples.
     */
    public function paginate_samples(int $page = 1, ?int $per_page = null, array $filters = [], array $ordering = []): array
    {
        return $this->paginate(
            [
                'with' => [
                    'status',
                    'bills',
                    'seller' => function (RelationInterface $relation) {
                        $table = $relation->getRelated()->getTable();
                        $builder = $relation->getQuery();
                        $primaryKey = $relation->getExistenceCompareKey();
                        /** @var Company_Model $companies */
                        $companies = model(Company_Model::class);
                        $companiesTable = $companies->get_company_table();
                        $builder
                            ->leftJoin($table, $companiesTable, $companiesTable, "`{$companiesTable}`.`id_user` = {$primaryKey}")
                            ->select(
                                $primaryKey,
                                "`{$table}`.`user_photo` AS `photo`",
                                "TRIM(CONCAT(`{$table}`.`fname`, ' ', `{$table}`.`lname`)) as `fullname`",
                                "`{$companiesTable}`.`{$companies->get_company_table_primary_key()}`",
                                "`{$companiesTable}`.`name_company` as `company_name`",
                                "`{$companiesTable}`.`legal_name_company` as `legal_company_name`",
                                "`{$companiesTable}`.`logo_company` as `logo`",
                                "`{$companiesTable}`.`index_name` as `slug`",
                                "`{$companiesTable}`.`type_company` as `type`",
                                "`{$table}`.`user_group` AS `group`",
                            )
                            ->andWhere(
                                $builder->expr()->eq(
                                    "`{$companiesTable}`.`type_company`",
                                    $builder->createNamedParameter('company', ParameterType::STRING, ':companyType')
                                )
                            )
                        ;
                    },
                    'buyer' => function (RelationInterface $relation) {
                        $table = $relation->getRelated()->getTable();
                        $builder = $relation->getQuery();
                        $primaryKey = $relation->getExistenceCompareKey();
                        $builder
                            ->select(
                                $primaryKey,
                                "`{$table}`.`user_photo` AS `photo`",
                                "TRIM(CONCAT(`{$table}`.`fname`, ' ', `{$table}`.`lname`)) as `fullname`",
                                "`{$table}`.`user_group` AS `group`",
                            )
                        ;
                    },
                ],
                'conditions' => $filters,
                'order'      => $ordering,
            ],
            $per_page ?? $this->getPerPage(),
            $page ?? 1
        );
    }

    /**
     * Returns detailed information about sample.
     */
    public function get_detailed_sample(?int $sample_id, array $filter_conditions = []): ?array
    {
        return null === $sample_id ? null : $this->findOneBy([
            'with'       => [
                'bill',
                'status',
                'seller' => function (RelationInterface $relation) {
                    $table = $relation->getRelated()->getTable();
                    $builder = $relation->getQuery();
                    $primaryKey = $relation->getExistenceCompareKey();
                    /** @var Company_Model $companies */
                    $companies = model(Company_Model::class);
                    $companiesTable = $companies->get_company_table();
                    $builder
                        ->leftJoin($table, $companiesTable, $companiesTable, "`{$companiesTable}`.`id_user` = {$primaryKey}")
                        ->select(
                            $primaryKey,
                            "`{$table}`.`user_photo` AS `photo`",
                            "TRIM(CONCAT(`{$table}`.`fname`, ' ', `{$table}`.`lname`)) as `fullname`",
                            "`{$companiesTable}`.`name_company` as `company_name`",
                            "`{$companiesTable}`.`legal_name_company` as `legal_company_name`",
                            "`{$companiesTable}`.`logo_company` as `logo`",
                            "`{$companiesTable}`.`index_name` as `slug`",
                            "`{$companiesTable}`.`type_company` as `type`",
                            "`{$table}`.`user_group` AS `group`",
                        )
                        ->andWhere(
                            $builder->expr()->eq(
                                "`{$companiesTable}`.`type_company`",
                                $builder->createNamedParameter('company', ParameterType::STRING, ':companyType')
                            )
                        )
                    ;
                },
                'buyer' => function (RelationInterface $relation) {
                    $table = $relation->getRelated()->getTable();
                    $builder = $relation->getQuery();
                    $primaryKey = $relation->getExistenceCompareKey();
                    /** @var Company_Buyer_Model $companies */
                    $companies = model(Company_Buyer_Model::class);
                    $companiesTable = $companies->get_company_table();
                    $builder
                        ->leftJoin($table, $companiesTable, $companiesTable, "`{$companiesTable}`.`id_user` = {$primaryKey}")
                        ->select(
                            $primaryKey,
                            "`{$table}`.`user_photo` AS `photo`",
                            "TRIM(CONCAT(`{$table}`.`fname`, ' ', `{$table}`.`lname`)) as `fullname`",
                            "`{$companiesTable}`.`company_name`",
                            "`{$companiesTable}`.`company_legal_name` as `legal_company_name`",
                            "`{$table}`.`user_group` AS `group`",
                        )
                    ;
                },
                'shipper' => function (RelationInterface $relation) {
                    $table = $relation->getRelated()->getTable();
                    $builder = $relation->getQuery();
                    $primaryKey = $relation->getExistenceCompareKey();
                    $builder->select(
                        $primaryKey,
                        "`{$table}`.`shipper_original_name` AS `name`",
                        "`{$table}`.`shipper_logo` AS `image`",
                        "`{$table}`.`shipper_website` AS `url`",
                        "`{$table}`.`shipper_contacts` AS `contact_url`",
                    );
                },
            ],
            'conditions' => array_replace($filter_conditions, ['sample' => $sample_id]),
        ]);
    }

    /**
     * Scope a query to bind statuses to query.
     */
    protected function bindStatuses(QueryBuilder $builder): void
    {
        /** @var Sample_Orders_Statuses_Model $statuses */
        $statuses = model(Sample_Orders_Statuses_Model::class);
        $builder->leftJoin(
            $this->getTable(),
            $statuses->getTable(),
            null,
            "{$this->getTable()}.id_status = {$statuses->getTable()}.{$statuses->getPrimaryKey()}"
        );
    }

    /**
     * Scope a query to filter by sample order ID.
     *
     * @uses Sample_Orders_Model::scopeOrder()
     */
    protected function scopeSample(QueryBuilder $builder, int $sample_id): void
    {
        $this->scopeOrder($builder, $sample_id);
    }

    /**
     * Scope a query to filter by sample ID.
     */
    protected function scopeOrder(QueryBuilder $builder, int $order_id): void
    {
        $this->scopePrimaryKey($builder, $this->getTable(), $this->getPrimaryKey(), $order_id);
    }

    /**
     * Scope a query to filter by seller ID.
     */
    protected function scopeSeller(QueryBuilder $builder, int $seller_id): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.id_seller",
                $builder->createNamedParameter((int) $seller_id, ParameterType::INTEGER, $this->nameScopeParameter('sellerId'))
            )
        );
    }

    /**
     * Scope a query to filter by buyer ID.
     */
    protected function scopeBuyer(QueryBuilder $builder, int $buyer_id): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.id_buyer",
                $builder->createNamedParameter((int) $buyer_id, ParameterType::INTEGER, $this->nameScopeParameter('buyerId'))
            )
        );
    }

    /**
     * Scope a query to filter records where buyer is assigned.
     */
    protected function scopeRequireBuyerAssigned(QueryBuilder $builder): void
    {
        $builder->andWhere(
            $builder->expr()->isNotNull("{$this->getTable()}.id_buyer")
        );
    }

    /**
     * Scope a query to filter by shipper ID.
     */
    protected function scopeShipper(QueryBuilder $builder, int $shipper_id): void
    {
        if (0 === $shipper_id) {
            $builder->andWhere(
                $builder->expr()->isNull("{$this->getTable()}.id_shipper")
            );
        } else {
            $builder->andWhere(
                $builder->expr()->eq(
                    "{$this->getTable()}.id_shipper",
                    $builder->createNamedParameter((int) $shipper_id, ParameterType::INTEGER, $this->nameScopeParameter('shipperId'))
                )
            );
        }
    }

    /**
     * Scope a query to filter by owner ID.
     */
    protected function scopeOwnedBy(QueryBuilder $builder, int $user_id): void
    {
        $parameter = $builder->createNamedParameter((int) $user_id, ParameterType::INTEGER, $this->nameScopeParameter('ownerId'));
        $builder->andWhere(
            $builder->expr()->or(
                $builder->expr()->eq("{$this->getTable()}.id_seller", $parameter),
                $builder->expr()->eq("{$this->getTable()}.id_buyer", $parameter)
            )
        );
    }

    /**
     * Scope a query to filter by status ID.
     */
    protected function scopeAssignedBuyer(QueryBuilder $builder, bool $is_assigned_to_buyer): void
    {
        $builder->andWhere(
            $is_assigned_to_buyer
                ? $builder->expr()->isNotNull("{$this->getTable()}.id_buyer")
                : $builder->expr()->isNull("{$this->getTable()}.id_buyer")
        );
    }

    /**
     * Scope a query to filter by status ID.
     */
    protected function scopeStatus(QueryBuilder $builder, int $status_id): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.id_status",
                $builder->createNamedParameter((int) $status_id, ParameterType::INTEGER, $this->nameScopeParameter('statusId'))
            )
        );
    }

    /**
     * Scope a query to filter by text search.
     */
    protected function scopeSearch(QueryBuilder $builder, string $text): void
    {
        $search_text = (new UnicodeString($text))->trim();
        $search_tokens = $this->tokenizeSearchText($search_text, true);
        $use_match_search = !empty($search_tokens);
        if ($use_match_search) {
            $parameter = $builder->createNamedParameter(
                $this->getConnection()->quote(implode(' ', $search_tokens)),
                ParameterType::STRING,
                $this->nameScopeParameter('searchMatchedText')
            );

            $builder->andWhere("MATCH ({$this->getTable()}.search_tokens) AGAINST ({$parameter} IN BOOLEAN MODE)");
        } else {
            $expressions = $builder->expr();
            $builder->andWhere(
                $expressions->or(
                    $expressions->eq(
                        "{$this->getTable()}.search_tokens",
                        $builder->createNamedParameter(
                            (string) $search_text,
                            ParameterType::STRING,
                            $this->nameScopeParameter('searchText')
                        )
                    ),
                    $expressions->like(
                        "{$this->getTable()}.search_tokens",
                        $builder->createNamedParameter(
                            (string) $search_text->prepend('%')->append('%'),
                            ParameterType::STRING,
                            $this->nameScopeParameter('searchTextToken')
                        )
                    ),
                )
            );
        }
    }

    /**
     * Scope a query to filter by keywords.
     */
    protected function scopeKeywords(QueryBuilder $builder, string $keywords): void
    {
        $this->scopeSearch($builder, $keywords);
    }

    /**
     * Scope a query to filter records by final price.
     */
    protected function scopeFinalPriceTo(QueryBuilder $builder, Money $price): void
    {
        $builder->andWhere(
            $builder->expr()->lte(
                "{$this->getTable()}.final_price",
                $builder->createNamedParameter(\moneyToDecimal($price), ParameterType::STRING, $this->nameScopeParameter('finalPriceTo'))
            )
        );
    }

    /**
     * Scope a query to filter records by final price.
     */
    protected function scopeFinalPriceFrom(QueryBuilder $builder, Money $price): void
    {
        $builder->andWhere(
            $builder->expr()->gte(
                "{$this->getTable()}.final_price",
                $builder->createNamedParameter(\moneyToDecimal($price), ParameterType::STRING, $this->nameScopeParameter('finalPriceFrom'))
            )
        );
    }

    /**
     * Scope a query to filter records by creation date.
     *
     * @param mixed $creation_date
     */
    protected function scopeCreationFrom(QueryBuilder $builder, $creation_date): void
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($creation_date, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->getTable()}.creation_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('createdFrom'))
            )
        );
    }

    /**
     * Scope a query to filter records by creation date.
     *
     * @param mixed $creation_date
     */
    protected function scopeCreationTo(QueryBuilder $builder, $creation_date): void
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($creation_date, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->getTable()}.creation_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('createdTo'))
            )
        );
    }

    /**
     * Scope a query to filter records by update date.
     *
     * @param mixed $updated_date
     */
    protected function scopeUpdateFrom(QueryBuilder $builder, $updated_date): void
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_date, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->getTable()}.update_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('updatedFrom'))
            )
        );
    }

    /**
     * Scope a query to filter records by update date.
     *
     * @param mixed $updated_date
     */
    protected function scopeUpdateTo(QueryBuilder $builder, $updated_date): void
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_date, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->getTable()}.update_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('updatedTo'))
            )
        );
    }

    /**
     * Resolves static relationships with seller.
     */
    protected function seller(): RelationInterface
    {
        /** @var User_Model $users */
        $users = model(User_Model::class);

        return $this->belongsTo(
            new PortableModel($this->getHandler(), $users->get_users_table(), $users->get_users_table_primary_key()),
            'id_seller'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with buyer.
     */
    protected function buyer(): RelationInterface
    {
        /** @var User_Model $users */
        $users = model(User_Model::class);

        return $this->belongsTo(
            new PortableModel($this->getHandler(), $users->get_users_table(), $users->get_users_table_primary_key()),
            'id_buyer'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with shipper.
     */
    protected function shipper(): RelationInterface
    {
        /** @var Ishippers_Model $shippers */
        $shippers = model(Ishippers_Model::class);

        return $this->belongsTo(
            new PortableModel($this->getHandler(), $shippers->get_shippers_table(), $shippers->get_shippers_table_primary_key()),
            'id_shipper'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with invoice.
     *
     * @deprecated
     */
    protected function invoice(): RelationInterface
    {
        /** @var Invoices_Model $invoices */
        $invoices = model(Invoices_Model::class);

        return $this->belongsTo(
            new PortableModel($this->getHandler(), $invoices->get_invoices_table(), $invoices->get_invoices_table_primary_key()),
            'id_invoice'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with bill.
     */
    protected function bill(): RelationInterface
    {
        /** @var Billing_Model $billing */
        $billing = model(Billing_Model::class);
        $relationship = $this->hasOne(
            new PortableModel($this->getHandler(), $billing->get_billing_table(), 'id_bill'),
            'id_item'
        );
        $relationship->disableNativeCast();
        $relationship
            ->getQuery()
            ->andWhere(
                "id_type_bill = {$relationship->getQuery()->createNamedParameter(static::ORDER_BILL_TYPE, ParameterType::INTEGER, ':billType')}"
            )
        ;

        return $relationship;
    }

    /**
     * Resolves static relationships with bills.
     */
    protected function bills(): RelationInterface
    {
        /** @var Billing_Model $billing */
        $billing = model(Billing_Model::class);
        $relationship = $this->hasMany(new PortableModel($this->getHandler(), $billing->get_billing_table(), 'id_bill'), 'id_item');
        $relationship->disableNativeCast();
        $relationship
            ->getQuery()
            ->andWhere(
                "id_type_bill = {$relationship->getQuery()->createNamedParameter(static::ORDER_BILL_TYPE, ParameterType::INTEGER, ':billType')}"
            )
        ;

        return $relationship;
    }

    /**
     * Resolves static relationships with status.
     */
    protected function status(): RelationInterface
    {
        return $this->belongsTo(Sample_Orders_Statuses_Model::class, 'id_status')->disableNativeCast();
    }

    /**
     * Resolves static relationships with departure country.
     */
    protected function departureCountry(): RelationInterface
    {
        /** @var Country_Model $countries */
        $countries = model(Country_Model::class);

        return $this->belongsTo(
            new PortableModel($this->getHandler(), $countries->get_countries_table(), $countries->get_countries_table_primary_key()),
            'ship_from_country'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with destination country.
     */
    protected function destinationCountry(): RelationInterface
    {
        /** @var Country_Model $countries */
        $countries = model(Country_Model::class);

        return $this->belongsTo(
            new PortableModel($this->getHandler(), $countries->get_countries_table(), $countries->get_countries_table_primary_key()),
            'ship_to_country'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with departure region.
     */
    protected function departureRegion(): RelationInterface
    {
        /** @var Country_Model $regions */
        $regions = model(Country_Model::class);

        return $this->belongsTo(
            new PortableModel($this->getHandler(), $regions->get_regions_table(), $regions->get_regions_table_primary_key()),
            'ship_from_state'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with destination region.
     */
    protected function destinationRegion(): RelationInterface
    {
        /** @var Country_Model $regions */
        $regions = model(Country_Model::class);

        return $this->belongsTo(
            new PortableModel($this->getHandler(), $regions->get_regions_table(), $regions->get_regions_table_primary_key()),
            'ship_to_state'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with departure city.
     */
    protected function departureCity(): RelationInterface
    {
        /** @var Country_Model $cities */
        $cities = model(Country_Model::class);

        return $this->belongsTo(
            new PortableModel($this->getHandler(), $cities->get_cities_table(), $cities->get_cities_table_primary_key()),
            'ship_from_city'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with destination city.
     */
    protected function destinationCity(): RelationInterface
    {
        /** @var Country_Model $cities */
        $cities = model(Country_Model::class);

        return $this->belongsTo(
            new PortableModel($this->getHandler(), $cities->get_cities_table(), $cities->get_cities_table_primary_key()),
            'ship_to_city'
        )->disableNativeCast();
    }
}

// End of file sample_orders_model.php
// Location: /tinymvc/myapp/models/sample_orders_model.php
