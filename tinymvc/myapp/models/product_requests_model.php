<?php

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
 * Product requests model.
 */
class Product_Requests_Model extends Model
{
    use Concerns\CanSearch;
    use Concerns\ConvertsAttributes;

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
    protected string $table = 'product_requests';

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
    protected array $guarded = array(
        'id',
        self::CREATED_AT,
        self::UPDATED_AT,
    );

    /**
     * {@inheritdoc}
     */
    protected array $casts = array(
        'id'                     => Types::INTEGER,
        'id_user'                => Types::INTEGER,
        'id_category'            => Types::INTEGER,
        'id_departure_country'   => Types::INTEGER,
        'id_destination_country' => Types::INTEGER,
        'quantity'               => Types::INTEGER,
        'start_price'            => CustomTypes::SIMPLE_MONEY,
        'final_price'            => CustomTypes::SIMPLE_MONEY,
        'email_meta'             => Types::JSON,
    );

    /**
     * {@inheritdoc}
     */
    protected array $nullable = array(
        'id_user',
        'id_category',
        'id_departure_country',
        'id_destination_country',
        'email',
        'name',
        'title',
        'details',
        'quantity',
        'start_price',
        'final_price',
        'email_meta',
        'creation_date',
        'update_date',
    );

    /**
     * Returns the paginator prepared for datagrid.
     */
    public function paginate_for_grid(?array $filters, ?array $ordering, ?int $per_page, ?int $page): array
    {
        return $this->paginate(
            array(
                'with'       => array(
                    'user'                => function (RelationInterface $relation) {
                        $table = $relation->getRelated()->getTable();
                        $primaryKey = $relation->getExistenceCompareKey();
                        $builder = $relation->getQuery();
                        $builder->select(
                            $primaryKey,
                            "$primaryKey as `id`",
                            "{$table}.`email`",
                            "{$table}.`email_status`",
                            "TRIM(CONCAT(`{$table}`.`fname`, ' ', `{$table}`.`lname`)) as `name`",
                        );
                    },
                    'category'            => function (RelationInterface $relation) {
                        $table = $relation->getRelated()->getTable();
                        $primaryKey = $relation->getExistenceCompareKey();
                        $builder = $relation->getQuery();
                        $builder->select(
                            $primaryKey,
                            "{$primaryKey} as `id`",
                            "`{$table}`.`name`",
                            "`{$table}`.`cat_childrens` as `children`",
                            "CONCAT('[', TRIM(`{$table}`.`breadcrumbs`) ,']') as `breadcrumbs`",
                        );
                    },
                    'departure_country'   => function (RelationInterface $relation) {
                        $table = $relation->getRelated()->getTable();
                        $primaryKey = $relation->getExistenceCompareKey();
                        $builder = $relation->getQuery();
                        $builder->select(
                            $primaryKey,
                            "`{$table}`.`country` as `name`",
                            "`{$table}`.`abr` as `iso3166`",
                        );
                    },
                    'destination_country' => function (RelationInterface $relation) {
                        $table = $relation->getRelated()->getTable();
                        $primaryKey = $relation->getExistenceCompareKey();
                        $builder = $relation->getQuery();
                        $builder->select(
                            $primaryKey,
                            "`{$table}`.`country` as `name`",
                            "`{$table}`.`abr` as `iso3166`",
                        );
                    },
                ),
                'order'      => $ordering ?? array(),
                'conditions' => $filters ?? array(),
            ),
            $per_page,
            $page
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

            $builder->andWhere(
                <<<CONDITION
                MATCH ({$this->getTable()}.name, {$this->getTable()}.email, {$this->getTable()}.title, {$this->getTable()}.details)
                AGAINST ({$parameter} IN BOOLEAN MODE)
                CONDITION
            );
        } else {
            $text_parameter = $builder->createNamedParameter(
                (string) $search_text,
                ParameterType::STRING,
                $this->nameScopeParameter('searchText')
            );
            $text_token_parameter = $builder->createNamedParameter(
                (string) $search_text->prepend('%')->append('%'),
                ParameterType::STRING,
                $this->nameScopeParameter('searchTextToken')
            );

            $expressions = $builder->expr();
            $builder->andWhere(
                $expressions->or(
                    $expressions->eq("{$this->getTable()}.title", $text_parameter),
                    $expressions->eq("{$this->getTable()}.details", $text_parameter),
                    $expressions->eq("{$this->getTable()}.name", $text_parameter),
                    $expressions->eq("{$this->getTable()}.email", $text_parameter),
                    $expressions->like("{$this->getTable()}.title", $text_token_parameter),
                    $expressions->like("{$this->getTable()}.details", $text_token_parameter),
                    $expressions->like("{$this->getTable()}.name", $text_token_parameter),
                    $expressions->like("{$this->getTable()}.email", $text_token_parameter),
                )
            );
        }
    }

    /**
     * Scope a query to filter by request ID.
     */
    protected function scopeRequest(QueryBuilder $builder, int $request_id): void
    {
        $this->scopePrimaryKey($builder, $this->getTable(), $this->getPrimaryKey(), $request_id);
    }

    /**
     * Scope a query to filter by user ID.
     */
    protected function scopeUser(QueryBuilder $builder, int $user_id): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.id_user",
                $builder->createNamedParameter((int) $user_id, ParameterType::INTEGER, $this->nameScopeParameter('userId'))
            )
        );
    }

    /**
     * Scope a query to filter by category ID.
     */
    protected function scopeCategory(QueryBuilder $builder, int $category_id): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.id_category",
                $builder->createNamedParameter((int) $category_id, ParameterType::INTEGER, $this->nameScopeParameter('categoryId'))
            )
        );
    }

    /**
     * Scope a query to filter by departure country ID.
     */
    protected function scopeDepartureCountry(QueryBuilder $builder, int $country_id): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.id_departure_country",
                $builder->createNamedParameter((int) $country_id, ParameterType::INTEGER, $this->nameScopeParameter('departureCountryId'))
            )
        );
    }

    /**
     * Scope a query to filter by destination country ID.
     */
    protected function scopeDestinationCountry(QueryBuilder $builder, int $country_id): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.id_destination_country",
                $builder->createNamedParameter((int) $country_id, ParameterType::INTEGER, $this->nameScopeParameter('destinationCountryId'))
            )
        );
    }

    /**
     * Scope a query to filter by minimal quantity.
     */
    protected function scopeQuantityFrom(QueryBuilder $builder, int $quantity): void
    {
        $builder->andWhere(
            $builder->expr()->gte(
                "{$this->getTable()}.quantity",
                $builder->createNamedParameter((int) $quantity, ParameterType::INTEGER, $this->nameScopeParameter('quantityFrom'))
            )
        );
    }

    /**
     * Scope a query to filter by maximum quantity.
     */
    protected function scopeQuantityTo(QueryBuilder $builder, int $quantity): void
    {
        $builder->andWhere(
            $builder->expr()->lte(
                "{$this->getTable()}.quantity",
                $builder->createNamedParameter((int) $quantity, ParameterType::INTEGER, $this->nameScopeParameter('quantityTo'))
            )
        );
    }

    /**
     * Scope a query to filter by minimal quantity.
     */
    protected function scopePriceFrom(QueryBuilder $builder, Money $price): void
    {
        $builder->andWhere(
            $builder->expr()->gte(
                "{$this->getTable()}.final_price",
                $builder->createNamedParameter(\moneyToDecimal($price), ParameterType::STRING, $this->nameScopeParameter('priceFrom'))
            )
        );
    }

    /**
     * Scope a query to filter by minimal quantity.
     */
    protected function scopePriceTo(QueryBuilder $builder, Money $price): void
    {
        $builder->andWhere(
            $builder->expr()->lte(
                "{$this->getTable()}.start_price",
                $builder->createNamedParameter(\moneyToDecimal($price), ParameterType::STRING, $this->nameScopeParameter('priceTo'))
            )
        );
    }

    /**
     * Scope a query to filter by creation date from.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeCreatedFromDate(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at, 'Y-m-d'))) {
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
     * Scope a query to filter by creation date to.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeCreatedToDate(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at, 'Y-m-d'))) {
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
     * Scope a query to filter by update date from.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeUpdatedFromDate(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at, 'Y-m-d'))) {
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
     * Scope a query to filter by update date to.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeUpdatedToDate(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at, 'Y-m-d'))) {
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
     * Resolves static relationships with user.
     */
    protected function user(): RelationInterface
    {
        /** @var User_Model $users */
        $users = model(User_Model::class);

        return $this->belongsTo(
            new PortableModel($this->getHandler(), $users->get_users_table(), $users->get_users_table_primary_key()),
            'id_user'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with category.
     */
    protected function category(): RelationInterface
    {
        /** @var Category_Model $categories */
        $categories = model(Category_Model::class);

        return $this->belongsTo(
            new PortableModel($this->getHandler(), $categories->get_categories_table(), $categories->get_categories_table_primary_key()),
            'id_category'
        )->disableNativeCast();
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
            'id_departure_country'
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
            'id_destination_country'
        )->disableNativeCast();
    }
}

// End of file product_requests_model.php
// Location: /tinymvc/myapp/models/product_requests_model.php
