<?php

declare(strict_types=1);

use App\Common\Database\BaseModel;
use App\Common\Database\Concerns;
use App\Common\Database\Model;
use App\Common\Database\PortableModel;
use App\Common\Database\Relations\BelongsTo;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Model Estimates.
 */
class Shipping_estimates_Model extends BaseModel
{
    use Concerns\CanTransformValues;
    use Concerns\ConvertsAttributes;

    /**
     * List of columns in activity log table which is later used on create or update.
     * Each record must be an array that have filled keys:
     *  - name: is a name of the column
     *  - type: indicates the type of the column
     *  - fillable: indicates that column is fillable in NORMAL mode (columns with this flag set to `false` are fillable in FORCE mode).
     *
     * @var array
     */
    protected $estimate_columns_metadata = array(
        array('name' => 'id',                'fillable' => false, 'type' => 'int'),
        array('name' => 'id_buyer',          'fillable' => true,  'type' => 'int'),
        array('name' => 'id_seller',         'fillable' => true,  'type' => 'int'),
        array('name' => 'id_country_from',   'fillable' => true,  'type' => 'int'),
        array('name' => 'id_country_to',     'fillable' => true,  'type' => 'int'),
        array('name' => 'id_state_from',     'fillable' => true,  'type' => 'int'),
        array('name' => 'id_state_to',       'fillable' => true,  'type' => 'int'),
        array('name' => 'id_city_from',      'fillable' => true,  'type' => 'int'),
        array('name' => 'id_city_to',        'fillable' => true,  'type' => 'int'),
        array('name' => 'postal_code_from',  'fillable' => true,  'type' => 'string'),
        array('name' => 'postal_code_to',    'fillable' => true,  'type' => 'string'),
        array('name' => 'group_key',         'fillable' => true,  'type' => 'string'),
        array('name' => 'group_title',       'fillable' => true,  'type' => 'string'),
        array('name' => 'comment_buyer',     'fillable' => true,  'type' => 'string'),
        array('name' => 'items',             'fillable' => true,  'type' => 'array', 'nullable' => true),
        array('name' => 'type',              'fillable' => true,  'type' => 'string'),
        array('name' => 'is_saved',          'fillable' => true,  'type' => 'int'),
        array('name' => 'date_create',       'fillable' => false, 'type' => 'datetime'),
        array('name' => 'date_update',       'fillable' => false, 'type' => 'datetime'),
        array('name' => 'max_response_date', 'fillable' => true,  'type' => 'datetime'),
    );

    /**
     * List of columns in activity log table which is later used on create or update.
     * Each record must be an array that have filled keys:
     *  - name: is a name of the column
     *  - type: indicates the type of the column
     *  - fillable: indicates that column is fillable in NORMAL mode (columns with this flag set to `false` are fillable in FORCE mode).
     *
     * @var array
     */
    protected $estimate_response_columns_metadata = array(
        array('name' => 'id',                 'fillable' => false, 'type' => 'int'),
        array('name' => 'id_shipper',         'fillable' => true,  'type' => 'int'),
        array('name' => 'id_estimate',        'fillable' => true,  'type' => 'int'),
        array('name' => 'comment',            'fillable' => true,  'type' => 'string'),
        array('name' => 'price',              'fillable' => true,  'type' => 'decimal'),
        array('name' => 'created_at',         'fillable' => false, 'type' => 'datetime'),
        array('name' => 'updated_at',         'fillable' => false, 'type' => 'datetime'),
        array('name' => 'delivery_days_from', 'fillable' => true,  'type' => 'int'),
        array('name' => 'delivery_days_to',   'fillable' => true,  'type' => 'int'),
    );

    /**
     * Name of the shippers table.
     *
     * @var string
     */
    private $shippers_table = 'orders_shippers';

    /**
     * Alias of the shippers table.
     *
     * @var string
     */
    private $shippers_table_alias = 'SHIPPERS';

    /**
     * Name of the shippers-industries pivot table.
     *
     * @var string
     */
    private $relation_country_table = 'shipper_countries';

    /**
     * Alias of the shippers-industries pivot table.
     *
     * @var string
     */
    private $relation_country_table_alias = 'COUNTRIES_PIVOT';

    /**
     * Name of the estimates table.
     *
     * @var string
     */
    private $shipping_estimates_table = 'shipping_estimates';

    /**
     * Alias of the estimates table.
     *
     * @var string
     */
    private $shipping_estimates_table_alias = 'ESTIMATES';

    /**
     * Name of the estimates table.
     *
     * @var string
     */
    private $shipping_estimate_responses_table = 'shipping_estimates_responses';

    /**
     * Alias of the estimates table.
     *
     * @var string
     */
    private $shipping_estimate_responses_alias = 'ESTIMATE_RESPONSES';

    /**
     * Name of the users table.
     *
     * @var string
     */
    private $users_table = 'users';

    /**
     * Alias of the users table.
     *
     * @var string
     */
    private $users_table_alias = 'USERS';

    /**
     * Name of the users table.
     *
     * @var string
     */
    private $companies_table = 'company_base';

    /**
     * Alias of the users table.
     *
     * @var string
     */
    private $companies_table_alias = 'COMPANIES';

    /**
     * Name of the countries table.
     *
     * @var string
     */
    private $countries_table = 'port_country';

    /**
     * Alias of the countries table.
     *
     * @var string
     */
    private $countries_table_alias = 'COUNTRIES';

    /**
     * Name of the states table.
     *
     * @var string
     */
    private $states_table = 'states';

    /**
     * Alias of the states table.
     *
     * @var string
     */
    private $states_table_alias = 'STATES';

    /**
     * Name of the cities table.
     *
     * @var string
     */
    private $cities_table = 'zips';

    /**
     * Alias of the cities table.
     *
     * @var string
     */
    private $cities_table_alias = 'CITIES';

    public function get_estimate($estimate_id, array $params = array())
    {
        if (empty($params['columns'])) {
            $params['columns'] = array(
                "`{$this->shipping_estimates_table_alias}`.*",
                "IF(`{$this->shipping_estimates_table_alias}`.`max_response_date` > NOW(), 'active', 'expired') as `current_countdown`",
            );
        }

        return $this->findRecord(
            'estimate',
            $this->shipping_estimates_table,
            $this->shipping_estimates_table_alias,
            'id',
            $estimate_id,
            $params
        );
    }

    public function find_estimate(array $params = array())
    {
        if (empty($params['columns'])) {
            $params['columns'] = array(
                "`{$this->shipping_estimates_table_alias}`.*",
                "IF(`{$this->shipping_estimates_table_alias}`.`max_response_date` > NOW(), 'active', 'expired') as `current_countdown`",
            );
        }

        return $this->findRecord(
            'estimate',
            $this->shipping_estimates_table,
            $this->shipping_estimates_table_alias,
            null,
            null,
            $params
        );
    }

    public function get_estimates(array $params = array())
    {
        return $this->findRecords(
            'estimate',
            $this->shipping_estimates_table,
            $this->shipping_estimates_table_alias,
            $params
        );
    }

    public function get_shipping_estimates(array $params = array())
    {
        if (empty($params['columns'])) {
            $params['columns'] = array(
                "`{$this->shipping_estimates_table_alias}`.*",
                "if(`{$this->shipping_estimates_table_alias}`.`max_response_date` > NOW(), 'active', 'expired') as `current_countdown`",
            );
        }
        if (empty($params['with'])) {
            $params['with'] = array('buyer' => function (RelationInterface $relation) { $relation->getQuery()->select('`idu`, `fname`, `lname`, `user_photo`'); });
        }

        return $this->findRecords(
            'estimate',
            $this->shipping_estimates_table,
            $this->shipping_estimates_table_alias,
            $params
        );
    }

    public function get_buyer_estimates($buyer_id, $sellers = array())
    {
        $params = array(
            'conditions' => array(
                'buyer'   => (int) $buyer_id,
                'sellers' => $sellers,
            ),
            'order' => array('date_create' => 'DESC'),
        );

        return $this->findRecords(
            'estimate',
            $this->shipping_estimates_table,
            $this->shipping_estimates_table_alias,
            $params
        );
    }

    public function count_shipping_estimates(array $params = array())
    {
        unset($params['order'], $params['with'], $params['limit'], $params['skip']);
        if (!isset($params['columns'])) {
            $params['columns'] = array('COUNT(*) AS AGGREGATE');
        }

        $is_multiple = isset($params['multiple']) ? (bool) $params['multiple'] : false;
        $counters = $this->get_shipping_estimates($params);
        if ($is_multiple) {
            return $counters;
        }

        return (int) arrayGet($counters, '0.AGGREGATE', 0);
    }

    public function create_estimate(array $estimate, $force = false)
    {
        return $this->db->insert(
            $this->shipping_estimates_table,
            $this->recordAttributesToDatabaseValues(
                $estimate,
                $this->estimate_columns_metadata,
                $force
            )
        );
    }

    public function create_estimates(array $estimates, $force = false)
    {
        return $this->db->insert_batch(
            $this->shipping_estimates_table,
            $this->recordsListToDatabaseValues(
                $estimates,
                $this->estimate_columns_metadata,
                $force
            )
        );
    }

    public function update_estimate($estimate_id, array $estimate, $force = false)
    {
        $this->db->where("`{$this->shipping_estimates_table}`.`id` = ?", (int) $estimate_id);

        return $this->db->update(
            $this->shipping_estimates_table,
            $this->recordAttributesToDatabaseValues(
                $estimate,
                $this->estimate_columns_metadata,
                $force
            )
        );
    }

    public function delete_estimate($estimate_id)
    {
        $this->db->where("`{$this->shipping_estimates_table}`.`id` = ?", (int) $estimate_id);

        return $this->db->delete($this->shipping_estimates_table);
    }

    public function delete_estimates_from_basket($buyer_id, $seller_id)
    {
        $this->db->where("`{$this->shipping_estimates_table}`.`id_buyer` = ?", (int) $buyer_id);
        $this->db->where("`{$this->shipping_estimates_table}`.`id_seller` = ?", (int) $seller_id);
        $this->db->where("`{$this->shipping_estimates_table}`.`type` = ?", 'basket');

        return $this->db->delete($this->shipping_estimates_table);
    }

    public function is_estimate_has_response($estimate_id)
    {
        return (bool) (int) arrayGet($this->get_estimate_responses(array(
            'columns'    => array('COUNT(*) AS `AGGREGATE`'),
            'conditions' => array(
                'request'  => (int) $estimate_id,
            ),
        )), '0.AGGREGATE', 0);
    }

    public function is_estimate_has_shipper_response($shipper_id, $estimate_id)
    {
        return (bool) (int) arrayGet($this->get_estimate_responses(array(
            'columns'    => array('COUNT(*) AS `AGGREGATE`'),
            'conditions' => array(
                'shipper'  => (int) $shipper_id,
                'request'  => (int) $estimate_id,
            ),
        )), '0.AGGREGATE', 0);
    }

    public function get_estimate_response($response_id, array $params = array())
    {
        return $this->findRecord(
            'estimate_response',
            $this->shipping_estimate_responses_table,
            $this->shipping_estimate_responses_alias,
            'id',
            (int) $response_id,
            $params
        );
    }

    public function get_estimate_responses(array $params = array())
    {
        return $this->findRecords(
            'estimate_response',
            $this->shipping_estimate_responses_table,
            $this->shipping_estimate_responses_alias,
            $params
        );
    }

    public function count_estimate_responses(array $params = array())
    {
        unset($params['order'], $params['with'], $params['limit'], $params['skip']);
        if (!isset($params['columns'])) {
            $params['columns'] = array('COUNT(*) AS AGGREGATE');
        }

        $is_multiple = isset($params['multiple']) ? (bool) $params['multiple'] : false;
        $counters = $this->get_estimate_responses($params);
        if ($is_multiple) {
            return $counters;
        }

        return (int) arrayGet($counters, '0.AGGREGATE', 0);
    }

    public function create_estimate_response($shipper_id, $estimate_id, array $response, $force = false)
    {
        return $this->db->insert(
            $this->shipping_estimate_responses_table,
            $this->recordAttributesToDatabaseValues(
                array_merge(array('id_estimate' => (int) $estimate_id, 'id_shipper' => (int) $shipper_id), $response),
                $this->estimate_response_columns_metadata,
                $force
            )
        );
    }

    public function create_estimate_responses(array $responses, $force = false)
    {
        return $this->db->insert_batch(
            $this->shipping_estimate_responses_table,
            $this->recordsListToDatabaseValues(
                $responses,
                $this->estimate_response_columns_metadata,
                $force
            )
        );
    }

    public function update_estimate_response($response_id, array $response, $force = false)
    {
        $this->db->where("`{$this->shipping_estimate_responses_table}`.`id` = ?", (int) $response_id);

        return $this->db->update(
            $this->shipping_estimate_responses_table,
            $this->recordAttributesToDatabaseValues(
                $response,
                $this->estimate_response_columns_metadata,
                $force
            )
        );
    }

    public function delete_estimate_response($response_id)
    {
        $this->db->where("`{$this->shipping_estimate_responses_table}`.`id` = ?", (int) $response_id);

        return $this->db->delete($this->shipping_estimate_responses_table);
    }

    /**
     * Scope a query to filter by estimate seller ID.
     *
     * @param int $seller
     */
    protected function scopeEstimateSeller(QueryBuilder $builder, $seller)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->shipping_estimates_table_alias}.id_seller",
                $builder->createNamedParameter((int) $seller, ParameterType::INTEGER, $this->nameScopeParameter('estimateSellerId'))
            )
        );
    }

    /**
     * Scope a query to filter by estimate sellers IDs.
     *
     * @param int[]|string[] $sellers
     */
    protected function scopeEstimateSellers(QueryBuilder $builder, $sellers)
    {
        if (empty($sellers)) {
            return;
        }

        $list = array_map('intval', getArrayFromString($sellers));

        $builder->andWhere(
            $builder->expr()->in(
                "{$this->shipping_estimates_table_alias}.id_seller",
                array_map(
                    fn (int $index, $seller) => $builder->createNamedParameter(
                        (int) $seller,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("estimateSellersIds{$index}")
                    ),
                    array_keys($list),
                    $list
                )
            )
        );
    }

    /**
     * Scope a query to filter by estimate buyer ID.
     *
     * @param int $buyer
     */
    protected function scopeEstimateBuyer(QueryBuilder $builder, $buyer)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->shipping_estimates_table_alias}.id_buyer",
                $builder->createNamedParameter((int) $buyer, ParameterType::INTEGER, $this->nameScopeParameter('estimateBuyerId'))
            )
        );
    }

    /**
     * Scope a query to filter by shipper estimate group key.
     *
     * @param string $group
     */
    protected function scopeEstimateGroup(QueryBuilder $builder, $group)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->shipping_estimates_table_alias}.group_key",
                $builder->createNamedParameter($group, ParameterType::STRING, $this->nameScopeParameter('estimateGroupKey'))
            )
        );
    }

    /**
     * Scope a query to filter by initial country.
     *
     * @param int $country
     */
    protected function scopeEstimateFromCountry(QueryBuilder $builder, $country)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->shipping_estimates_table_alias}.id_country_from",
                $builder->createNamedParameter((int) $country, ParameterType::INTEGER, $this->nameScopeParameter('estimateFromCountryId'))
            )
        );
    }

    /**
     * Scope a query to filter by initial state.
     *
     * @param int $state
     */
    protected function scopeEstimateFromState(QueryBuilder $builder, $state)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->shipping_estimates_table_alias}.id_state_from",
                $builder->createNamedParameter((int) $state, ParameterType::INTEGER, $this->nameScopeParameter('estimateFromStateId'))
            )
        );
    }

    /**
     * Scope a query to filter by initial city.
     *
     * @param int $city
     */
    protected function scopeEstimateFromCity(QueryBuilder $builder, $city)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->shipping_estimates_table_alias}.id_city_from",
                $builder->createNamedParameter((int) $city, ParameterType::INTEGER, $this->nameScopeParameter('estimateFromCityId'))
            )
        );
    }

    /**
     * Scope a query to filter by final country.
     *
     * @param int $country
     */
    protected function scopeEstimateToCountry(QueryBuilder $builder, $country)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->shipping_estimates_table_alias}.id_country_to",
                $builder->createNamedParameter((int) $country, ParameterType::INTEGER, $this->nameScopeParameter('estimateToCountryId'))
            )
        );
    }

    /**
     * Scope a query to filter by final state.
     *
     * @param int $state
     */
    protected function scopeEstimateToState(QueryBuilder $builder, $state)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->shipping_estimates_table_alias}.id_state_to",
                $builder->createNamedParameter((int) $state, ParameterType::INTEGER, $this->nameScopeParameter('estimateToStateId'))
            )
        );
    }

    /**
     * Scope a query to filter by final city.
     *
     * @param int $city
     */
    protected function scopeEstimateToCity(QueryBuilder $builder, $city)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->shipping_estimates_table_alias}.id_city_to",
                $builder->createNamedParameter((int) $city, ParameterType::INTEGER, $this->nameScopeParameter('estimateToCityId'))
            )
        );
    }

    /**
     * Scope a query to filter by saved flag.
     *
     * @param bool|int $is_saved
     */
    protected function scopeEstimateIsSaved(QueryBuilder $builder, $is_saved)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->shipping_estimates_table_alias}.is_saved",
                $builder->createNamedParameter((int) $is_saved, ParameterType::INTEGER, $this->nameScopeParameter('estimateIsSaved'))
            )
        );
    }

    /**
     * Scope a query to filter by shipper estimate responded status.
     */
    protected function scopeEstimateProcessedResponses(QueryBuilder $builder)
    {
        $subquery_builder = $this->createQueryBuilder();
        $subquery_builder
            ->select('*')
            ->from($this->shipping_estimate_responses_table, $this->shipping_estimate_responses_alias)
            ->where(
                $subquery_builder->expr()->eq(
                    "{$this->shipping_estimates_table_alias}.id",
                    "{$this->shipping_estimate_responses_alias}.id_estimate"
                )
            )
        ;

        $builder->andWhere("EXISTS ({$subquery_builder->getSQL()})");
    }

    /**
     * Scope a query to filter by shipper estimate responded status.
     *
     * @param bool|int $shipper
     */
    protected function scopeEstimateShipperProcessedResponses(QueryBuilder $builder, $shipper)
    {
        $subquery_builder = $this->createQueryBuilder();
        $subquery_builder
            ->select('*')
            ->from($this->shipping_estimate_responses_table, $this->shipping_estimate_responses_alias)
            ->where(
                $subquery_builder->expr()->eq(
                    "{$this->shipping_estimates_table_alias}.id",
                    "{$this->shipping_estimate_responses_alias}.id_estimate"
                )
            )
            ->andWhere(
                $subquery_builder->expr()->eq(
                    "{$this->shipping_estimate_responses_alias}.id_shipper",
                    $builder->createNamedParameter(
                        (int) $shipper,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter('estimateProcessedShipperId')
                    )
                )
            )
        ;

        $builder->andWhere("EXISTS ({$subquery_builder->getSQL()})");
    }

    /**
     * Scope a query to filter by shipper estimate not responded status.
     */
    protected function scopeEstimateAwaitingResponses(QueryBuilder $builder)
    {
        $subquery_builder = $this->createQueryBuilder();
        $subquery_builder
            ->select('*')
            ->from($this->shipping_estimate_responses_table, $this->shipping_estimate_responses_alias)
            ->where(
                $subquery_builder->expr()->eq(
                    "{$this->shipping_estimates_table_alias}.id",
                    "{$this->shipping_estimate_responses_alias}.id_estimate"
                )
            )
        ;

        $builder->andWhere(
            $builder->expr()->and(
                $builder->expr()->gt("{$this->shipping_estimates_table_alias}.max_response_date", 'NOW()'),
                "NOT EXISTS ({$subquery_builder->getSQL()})"
            )
        );
    }

    /**
     * Scope a query to filter by shipper estimate not responded status.
     *
     * @param int $shipper
     */
    protected function scopeEstimateShipperAwaitingResponses(QueryBuilder $builder, $shipper)
    {
        $subquery_builder = $this->createQueryBuilder();
        $subquery_builder
            ->select('*')
            ->from($this->shipping_estimate_responses_table, $this->shipping_estimate_responses_alias)
            ->where(
                $subquery_builder->expr()->eq(
                    "{$this->shipping_estimates_table_alias}.id",
                    "{$this->shipping_estimate_responses_alias}.id_estimate"
                )
            )
            ->andWhere(
                $subquery_builder->expr()->eq(
                    "{$this->shipping_estimate_responses_alias}.id_shipper",
                    $builder->createNamedParameter(
                        (int) $shipper,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter('estimateAwaitingShipperId')
                    )
                )
            )
        ;

        $builder->andWhere(
            $builder->expr()->and(
                $builder->expr()->gt("{$this->shipping_estimates_table_alias}.max_response_date", 'NOW()'),
                "NOT EXISTS ({$subquery_builder->getSQL()})"
            )
        );
    }

    /**
     * Scope a query to filter by shipper estimate expired status.
     */
    protected function scopeEstimateExpiredResponses(QueryBuilder $builder)
    {
        $subquery_builder = $this->createQueryBuilder();
        $subquery_builder
            ->select('*')
            ->from($this->shipping_estimate_responses_table, $this->shipping_estimate_responses_alias)
            ->where(
                $subquery_builder->expr()->eq(
                    "{$this->shipping_estimates_table_alias}.id",
                    "{$this->shipping_estimate_responses_alias}.id_estimate"
                )
            )
        ;

        $builder->andWhere(
            $builder->expr()->and(
                $builder->expr()->lte("{$this->shipping_estimates_table_alias}.max_response_date", 'NOW()'),
                "NOT EXISTS ({$subquery_builder->getSQL()})"
            )
        );
    }

    /**
     * Scope a query to filter by shipper estimate expired status.
     *
     * @param int $shipper
     */
    protected function scopeEstimateShipperExpiredResponses(QueryBuilder $builder, $shipper)
    {
        $subquery_builder = $this->createQueryBuilder();
        $subquery_builder
            ->select('*')
            ->from($this->shipping_estimate_responses_table, $this->shipping_estimate_responses_alias)
            ->where(
                $subquery_builder->expr()->eq(
                    "{$this->shipping_estimates_table_alias}.id",
                    "{$this->shipping_estimate_responses_alias}.id_estimate"
                )
            )
            ->andWhere(
                $subquery_builder->expr()->eq(
                    "{$this->shipping_estimate_responses_alias}.id_shipper",
                    $builder->createNamedParameter(
                        (int) $shipper,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter('estimateExpiredShipperId')
                    )
                )
            )
        ;

        $builder->andWhere(
            $builder->expr()->and(
                $builder->expr()->lte("{$this->shipping_estimates_table_alias}.max_response_date", 'NOW()'),
                "NOT EXISTS ({$subquery_builder->getSQL()})"
            )
        );
    }

    /**
     * Scope a query to filter by shipper country.
     *
     * @param string $shipper
     */
    protected function scopeEstimateFromShipperCountries(QueryBuilder $builder, $shipper)
    {
        $subquery_builder = $this->createQueryBuilder();
        $subquery_builder
            ->select('*')
            ->from($this->relation_country_table, $this->relation_country_table_alias)
            ->where(
                $subquery_builder->expr()->eq(
                    "{$this->relation_country_table_alias}.id_user",
                    $builder->createNamedParameter(
                        (int) $shipper,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter('estimateShipperWithCountriesId')
                    )
                )
            )
            ->andWhere(
                $subquery_builder->expr()->or(
                    $subquery_builder->expr()->eq(
                        "{$this->relation_country_table_alias}.id_country",
                        $builder->createNamedParameter(
                            0,
                            ParameterType::INTEGER,
                            $this->nameScopeParameter('estimateShipperEmptyCountry')
                        )
                    ),
                    $subquery_builder->expr()->or(
                        $subquery_builder->expr()->eq(
                            "{$this->shipping_estimates_table_alias}.id_country_from",
                            "{$this->relation_country_table_alias}.id_country"
                        ),
                        $subquery_builder->expr()->eq(
                            "{$this->shipping_estimates_table_alias}.id_country_to",
                            "{$this->relation_country_table_alias}.id_country"
                        )
                    )
                )
            )
        ;

        $builder->andWhere("EXISTS ({$subquery_builder->getSQL()})");
    }

    /**
     * Scope a query to filter by creation datetime.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeEstimateCreatedAt(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->shipping_estimates_table_alias}.date_create",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('estimateCreatedAt'))
            )
        );
    }

    /**
     * Scope a query to filter by creation datetime from.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeEstimateCreatedFrom(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "{$this->shipping_estimates_table_alias}.date_create",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('estimateCreatedFrom'))
            )
        );
    }

    /**
     * Scope a query to filter by creation datetime to.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeEstimateCreatedTo(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "{$this->shipping_estimates_table_alias}.date_create",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('estimateCreatedTo'))
            )
        );
    }

    /**
     * Scope a query to filter by creation date.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeEstimateCreatedAtDate(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "DATE({$this->shipping_estimates_table_alias}.date_create)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('estimateCreatedAtDate'))
            )
        );
    }

    /**
     * Scope a query to filter by creation date from.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeEstimateCreatedFromDate(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->shipping_estimates_table_alias}.date_create)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('estimateCreatedFromDate'))
            )
        );
    }

    /**
     * Scope a query to filter by creation date to.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeEstimateCreatedToDate(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->shipping_estimates_table_alias}.date_create)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('estimateCreatedToDate'))
            )
        );
    }

    /**
     * Scope a query to filter by update datetime.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeEstimateUpdatedAt(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->shipping_estimates_table_alias}.date_update",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('estimateUpdatedAt'))
            )
        );
    }

    /**
     * Scope a query to filter by update datetime from.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeEstimateUpdatedFrom(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "{$this->shipping_estimates_table_alias}.date_update",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('estimateUpdatedFrom'))
            )
        );
    }

    /**
     * Scope a query to filter by update datetime to.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeEstimateUpdatedTo(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "{$this->shipping_estimates_table_alias}.date_update",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('estimateUpdatedTo'))
            )
        );
    }

    /**
     * Scope a query to filter by update date.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeEstimateUpdatedAtDate(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "DATE({$this->shipping_estimates_table_alias}.date_update)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('estimateUpdatedAtDate'))
            )
        );
    }

    /**
     * Scope a query to filter by update date from.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeEstimateUpdatedFromDate(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->shipping_estimates_table_alias}.date_update)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('estimateUpdatedFromDate'))
            )
        );
    }

    /**
     * Scope a query to filter by update date to.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeEstimateUpdatedToDate(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->shipping_estimates_table_alias}.date_update)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('estimateUpdatedToDate'))
            )
        );
    }

    /**
     * Scope a query to filter by sign datetime.
     *
     * @param \DateTimeInterface|int|string $countdown_at
     */
    protected function scopeEstimateCountdownAt(QueryBuilder $builder, $countdown_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($countdown_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->shipping_estimates_table_alias}.max_response_date",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('estimateCountdownAt'))
            )
        );
    }

    /**
     * Scope a query to filter by sign datetime from.
     *
     * @param \DateTimeInterface|int|string $countdown_at
     */
    protected function scopeEstimateCountdownFrom(QueryBuilder $builder, $countdown_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($countdown_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "{$this->shipping_estimates_table_alias}.max_response_date",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('estimateCountdownFrom'))
            )
        );
    }

    /**
     * Scope a query to filter by sign datetime to.
     *
     * @param \DateTimeInterface|int|string $countdown_at
     */
    protected function scopeEstimateCountdownTo(QueryBuilder $builder, $countdown_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($countdown_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "{$this->shipping_estimates_table_alias}.max_response_date",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('estimateCountdownTo'))
            )
        );
    }

    /**
     * Scope a query to filter by sign date.
     *
     * @param \DateTimeInterface|int|string $countdown_at
     */
    protected function scopeEstimateCountdownAtDate(QueryBuilder $builder, $countdown_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($countdown_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "DATE({$this->shipping_estimates_table_alias}.max_response_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('estimateCountdownAtDate'))
            )
        );
    }

    /**
     * Scope a query to filter by sign date from.
     *
     * @param \DateTimeInterface|int|string $countdown_at
     */
    protected function scopeEstimateCountdownFromDate(QueryBuilder $builder, $countdown_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($countdown_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->shipping_estimates_table_alias}.max_response_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('estimateCountdownFromDate'))
            )
        );
    }

    /**
     * Scope a query to filter by countdown time.
     *
     * @param \DateTimeInterface|int|string $countdown_at
     */
    protected function scopeEstimateCountdownToDate(QueryBuilder $builder, $countdown_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($countdown_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->shipping_estimates_table_alias}.max_response_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('estimateCountdownToDate'))
            )
        );
    }

    /**
     * Scope a query to filter estimate response shipper.
     *
     * @param int $shipper
     */
    protected function scopeEstimateResponseShipper(QueryBuilder $builder, $shipper)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->shipping_estimate_responses_alias}.id_shipper",
                $builder->createNamedParameter((int) $shipper, ParameterType::INTEGER, $this->nameScopeParameter('responseShipperId'))
            )
        );
    }

    /**
     * Scope a query to filter estimate response request ID.
     *
     * @param int $estimate
     */
    protected function scopeEstimateResponseRequest(QueryBuilder $builder, $estimate)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->shipping_estimate_responses_alias}.id_estimate",
                $builder->createNamedParameter((int) $estimate, ParameterType::INTEGER, $this->nameScopeParameter('responseEstimateId'))
            )
        );
    }

    /**
     * Scope a query to filter by keywords.
     *
     * @param string $keywords
     */
    protected function scopeEstimateSearch(QueryBuilder $builder, $keywords)
    {
        if (str_word_count_utf8($keywords) > 1) {
            $escaped_search_string = $this->db->getConnection()->quote(trim($keywords));
            $search_parts = preg_split('/\\b/', trim($escaped_search_string, "'"));
            $search_parts = array_map('trim', $search_parts);
            $search_parts = array_filter($search_parts);
            if (!empty($search_parts)) {
                // Drop array keys
                $search_parts = array_values($search_parts);
                $parameter = $builder->createNamedParameter(
                    $builder->expr()->literal(implode('* <', $search_parts) . '*'),
                    ParameterType::STRING,
                    $this->nameScopeParameter('eventSearchMatchedText')
                );

                $builder->andWhere(
                    <<<CONDITION
                    MATCH (
                        {$this->shipping_estimates_table_alias}.group_title,
                        {$this->shipping_estimates_table_alias}.comment_buyer
                    ) AGAINST ({$parameter} IN BOOLEAN MODE)
                    CONDITION
                );
            }
        } else {
            $text_parameter = $builder->createNamedParameter(
                $keywords,
                ParameterType::STRING,
                $this->nameScopeParameter('eventSearchText')
            );
            $text_token_parameter = $builder->createNamedParameter(
                "%{$keywords}%",
                ParameterType::STRING,
                $this->nameScopeParameter('eventSearchTextToken')
            );

            $expressions = $builder->expr();
            $builder->andWhere(
                $expressions->or(
                    $expressions->eq("{$this->shipping_estimates_table_alias}.group_title", $text_parameter),
                    $expressions->eq("{$this->shipping_estimates_table_alias}.comment_buyer", $text_parameter),
                    $expressions->like("{$this->shipping_estimates_table_alias}.group_title", $text_token_parameter),
                    $expressions->like("{$this->shipping_estimates_table_alias}.comment_buyer", $text_token_parameter),
                )
            );
        }
    }

    /**
     * Scope a query to bind users to the query.
     */
    protected function bindEstimateUsers(QueryBuilder $builder)
    {
        $builder->leftJoin(
            $this->shipping_estimates_table_alias,
            $this->users_table,
            $this->users_table_alias,
            "`{$this->shipping_estimates_table_alias}`.`id_buyer` = `{$this->users_table_alias}`.`idu`"
        );
    }

    /**
     * Resolves static relationships with buyer.
     */
    protected function estimateBuyer(): RelationInterface
    {
        return $this->belongsTo(
            new PortableModel($this->getHandler(), $this->users_table, 'idu'),
            'id_buyer'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with seller.
     */
    protected function estimateSeller(): RelationInterface
    {
        return $this->belongsTo(
            new PortableModel($this->getHandler(), $this->users_table, 'idu'),
            'id_seller'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with seller's company.
     */
    protected function estimateCompany(): RelationInterface
    {
        $relationship = $this->belongsTo(new PortableModel($this->getHandler(), $this->companies_table, 'id_company'), 'id_seller', 'id_user');
        $relationship->disableNativeCast();
        $relationship
            ->getQuery()
            ->andWhere(
                "parent_company = {$relationship->getQuery()->createNamedParameter(0, ParameterType::INTEGER, ':parentCompany')}"
            )
        ;

        return $relationship;
    }

    /**
     * Resolves static relationships with shipper.
     */
    protected function estimateResponses(): RelationInterface
    {
        return $this->hasMany(
            new PortableModel($this->getHandler(), $this->shipping_estimate_responses_table, 'id'),
            'id_estimate'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with shipping start country.
     */
    protected function estimateDepartureCountry(): RelationInterface
    {
        return $this->belongsTo(
            new PortableModel($this->getHandler(), $this->countries_table, 'id'),
            'id_country_from'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with shipping destination country.
     */
    protected function estimateDestinationCountry(): RelationInterface
    {
        return $this->belongsTo(
            new PortableModel($this->getHandler(), $this->countries_table, 'id'),
            'id_country_to'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with shipping start state.
     */
    protected function estimateDepartureState(): RelationInterface
    {
        return $this->belongsTo(
            new PortableModel($this->getHandler(), $this->states_table, 'id'),
            'id_state_from'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with shipping destination state.
     */
    protected function estimateDestinationState(): RelationInterface
    {
        return $this->belongsTo(
            new PortableModel($this->getHandler(), $this->states_table, 'id'),
            'id_state_to'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with shipping start city.
     */
    protected function estimateDepartureCity(): RelationInterface
    {
        return $this->belongsTo(
            new PortableModel($this->getHandler(), $this->cities_table, 'id'),
            'id_city_from'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with shipping destination city.
     */
    protected function estimateDestinationCity(): RelationInterface
    {
        return $this->belongsTo(
            new PortableModel($this->getHandler(), $this->cities_table, 'id'),
            'id_city_to'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships of estimate response with shippers.
     */
    protected function estimateResponseShipper(): RelationInterface
    {
        // Get the related instance.
        $related = new PortableModel($this->getHandler(), $this->shippers_table, 'id');
        // We doing this manually beacause there is no relaiable way
        // to create VALID relation due to how it is resolved in
        // self::resolveRelatedModel() method. Sadly this method doesn't know
        // how to resolve silces.
        return (new BelongsTo(
            $this->createQueryBuilder(),
            $related,
            new PortableModel($this->getHandler(), $this->shipping_estimate_responses_alias, 'id'),
            'id_shipper',
            $related->getPrimaryKey(),
            'estimateResponseShipper'
        ))->disableNativeCast();
    }

    /**
     * Creates new related instance of the model for relation.
     *
     * @param BaseModel|Model|string $source
     */
    protected function resolveRelatedModel($source): Model
    {
        if ($source === $this) {
            return new PortableModel($this->getHandler(), $this->shipping_estimates_table_alias, 'id');
        }

        return parent::resolveRelatedModel($source);
    }
}

// End of file estimates_model.php
// Location: /tinymvc/myapp/models/estimates_model.php
