<?php

declare(strict_types=1);

use App\Common\Database\BaseModel;
use App\Common\Database\Concerns;
use App\Common\Database\Model;
use App\Common\Database\PortableModel;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Model Orders_quotes.
 */
class Orders_quotes_Model extends BaseModel
{
    use Concerns\CanTransformValues;
    use Concerns\ConvertsAttributes;

    public $statuses = array(
        'awaiting' => array(
            'name' => 'New',
            'icon' => 'ep-icon ep-icon_hourglass-processing txt-orange',
        ),
        'confirmed' => array(
            'name' => 'Confirmed',
            'icon' => 'ep-icon ep-icon_ok-circle txt-green',
        ),
        'declined' => array(
            'name' => 'Not winning',
            'icon' => 'ep-icon ep-icon_minus-circle txt-red',
        ),
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
    protected $bid_columns_metadata = array(
        array('name' => 'id_quote',           'fillable' => false, 'type' => 'int'),
        array('name' => 'id_shipper',         'fillable' => true,  'type' => 'int'),
        array('name' => 'id_order',           'fillable' => true,  'type' => 'int'),
        array('name' => 'id_shipping_type',   'fillable' => true,  'type' => 'int'),
        array('name' => 'shipping_price',     'fillable' => true,  'type' => 'decimal'),
        array('name' => 'delivery_days_from', 'fillable' => true,  'type' => 'int'),
        array('name' => 'delivery_days_to',   'fillable' => true,  'type' => 'int'),
        array('name' => 'shipment_cfs',       'fillable' => true,  'type' => 'string'),
        array('name' => 'shipment_ff',        'fillable' => true,  'type' => 'string'),
        array('name' => 'shipment_pickup',    'fillable' => true,  'type' => 'enum'),
        array('name' => 'comment_shipper',    'fillable' => true,  'type' => 'string'),
        array('name' => 'comment_user',       'fillable' => true,  'type' => 'string'),
        array('name' => 'insurance_options',  'fillable' => true,  'type' => 'array', 'nullable' => true),
        array('name' => 'quote_status',       'fillable' => true,  'type' => 'enum'),
        array('name' => 'pickup_date',        'fillable' => true,  'type' => 'datetime'),
        array('name' => 'delivery_date',      'fillable' => true,  'type' => 'datetime'),
        array('name' => 'create_date',        'fillable' => false, 'type' => 'datetime'),
        array('name' => 'update_date',        'fillable' => false, 'type' => 'datetime'),
    );

    /**
     * The name of the orders table.
     *
     * @var string
     */
    private $orders_table = 'item_orders';

    /**
     * The alias of the orders table.
     *
     * @var string
     */
    private $orders_table_alias = 'ORDERS';

    /**
     * The name of the orders' statuses table.
     *
     * @var string
     */
    private $statuses_table = 'orders_status';

    /**
     * The alias of the orders' statuses table.
     *
     * @var string
     */
    private $statuses_table_alias = 'STATUSES';

    /**
     * The name of the orders' invoices table.
     *
     * @var string
     */
    private $invoices_table = 'item_order_invoices';

    /**
     * The alias of the orders' invoices table.
     *
     * @var string
     */
    private $invoices_table_alias = 'INVOICES';

    /**
     * The name of the shippers' bids table.
     *
     * @var string
     */
    private $shippers_bids_table = 'orders_shippers_quotes';

    /**
     * The alias of the shippers' bids table.
     *
     * @var string
     */
    private $shippers_bids_table_alias = 'BIDS';

    /**
     * The name of the shipping types table.
     *
     * @var string
     */
    private $shipping_types_table = 'shipping_type';

    /**
     * The alias of the shipping types table.
     *
     * @var string
     */
    private $shipping_types_table_alias = 'SHIPPING_TYPES';

    /**
     * The name of the shippers table.
     *
     * @var string
     */
    private $shippers_table = 'orders_shippers';

    /**
     * The alias of the shippers table.
     *
     * @var string
     */
    private $shippers_table_alias = 'SHIPPERS';

    /**
     * The name of the shippers table.
     *
     * @var string
     */
    private $users_table = 'users';

    /**
     * The alias of the shippers table.
     *
     * @var string
     */
    private $users_table_alias = 'USERS';

    public function get_bid($type_id, array $params = array())
    {
        return $this->findRecord(
            'bid',
            $this->shippers_bids_table,
            $this->shippers_bids_table_alias,
            'id_quote',
            $type_id,
            $params
        );
    }

    public function find_bid(array $params = array())
    {
        return $this->findRecord(
            'bid',
            $this->shippers_bids_table,
            $this->shippers_bids_table_alias,
            null,
            null,
            $params
        );
    }

    public function get_bids(array $params = array())
    {
        return $this->findRecords(
            'bid',
            $this->shippers_bids_table,
            $this->shippers_bids_table_alias,
            $params
        );
    }

    public function count_bids(array $params = array())
    {
        unset($params['order'], $params['with'], $params['limit'], $params['skip']);
        if (!isset($params['columns'])) {
            $params['columns'] = array('COUNT(*) AS AGGREGATE');
        }

        $is_multiple = isset($params['multiple']) ? (bool) $params['multiple'] : false;
        $counters = $this->get_bids($params);
        if ($is_multiple) {
            return $counters;
        }

        return (int) arrayGet($counters, '0.AGGREGATE', 0);
    }

    public function has_bid($shipper_id, $order_id)
    {
        $counter = $this->find_bid(array(
            'columns'    => array('COUNT(*) AS AGGREGATE'),
            'conditions' => array(
                'order'   => (int) $order_id,
                'shipper' => (int) $shipper_id,
            ),
        ));

        if (!$counter || empty($counter)) {
            return false;
        }

        return isset($counter['AGGREGATE']) ? (bool) (int) $counter['AGGREGATE'] : false;
    }

    public function create_bid(array $bid, $force = false)
    {
        if (empty($bid)) {
            return false;
        }

        return $this->db->insert(
            $this->shippers_bids_table,
            $this->recordAttributesToDatabaseValues(
                $bid,
                $this->bid_columns_metadata,
                $force
            )
        );
    }

    public function create_bids(array $bids, $force = false)
    {
        return $this->db->insert_batch(
            $this->shippers_bids_table,
            $this->recordsListToDatabaseValues(
                $bids,
                $this->bid_columns_metadata,
                $force
            )
        );
    }

    public function update_bid($bid_id, array $bid, $force = false)
    {
        if (empty($bid)) {
            return false;
        }

        $this->db->where("`{$this->shippers_bids_table}`.`id_quote` = ?", (int) $bid_id);

        return $this->db->update(
            $this->shippers_bids_table,
            $this->recordAttributesToDatabaseValues(
                $bid,
                $this->bid_columns_metadata,
                $force
            )
        );
    }

    public function delete_bid($bid_id)
    {
        $this->db->where("`{$this->shippers_bids_table}`.`id_quote` = ?", (int) $bid_id);

        return $this->db->delete($this->shippers_bids_table);
    }

    public function get_bid_users($bid_id)
    {
        $bid = $this->get_bid($bid_id, array('with' => array('order')));
        if (null === $bid) {
            return array();
        }

        return array_filter(array(
            'buyer'   => (int) arrayGet($bid, 'order.id_buyer', 0),
            'seller'  => (int) arrayGet($bid, 'order.id_seller', 0),
            'shipper' => (int) arrayGet($bid, 'id_shipper', function () use ($bid) { return arrayGet($bid, 'order.id_shipper', 0); }),
            'manager' => (int) arrayGet($bid, 'order.ep_manager', 0),
        ));
    }

    public function make_bids_expired()
    {
        $this->db->where('quote_status = ?', 'awaiting');
        $this->db->where_raw('delivery_date < NOW()');

        return $this->db->update($this->shippers_bids_table, array('quote_status' => 'declined'));
    }

    /**
     * Scope a query to bind shippers to the query.
     */
    protected function bindBidOrders(QueryBuilder $builder)
    {
        $builder->innerJoin(
            $this->shippers_bids_table_alias,
            $this->orders_table,
            $this->orders_table_alias,
            "`{$this->shippers_bids_table_alias}`.`id_order` = `{$this->orders_table_alias}`.`id`"
        );
    }

    /**
     * Scope a query to filter by bid ID.
     *
     * @param int $bid_id
     */
    protected function scopeBidId(QueryBuilder $builder, $bid_id)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->shippers_bids_table_alias}.id_quote",
                $builder->createNamedParameter((int) $bid_id, ParameterType::INTEGER, $this->nameScopeParameter('bidId'))
            )
        );
    }

    /**
     * Scope a query to filter by bid order ID.
     *
     * @param int $order_id
     */
    protected function scopeBidOrder(QueryBuilder $builder, $order_id)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->shippers_bids_table_alias}.id_order",
                $builder->createNamedParameter((int) $order_id, ParameterType::INTEGER, $this->nameScopeParameter('bidOrderId'))
            )
        );
    }

    /**
     * Scope a query to filter by order shipment type ID.
     *
     * @param int $shipment_type
     */
    protected function scopeBidOrderShipmentType(QueryBuilder $builder, $shipment_type)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->orders_table_alias}.shipment_type",
                $builder->createNamedParameter((int) $shipment_type, ParameterType::INTEGER, $this->nameScopeParameter('bidOrderShipmentType'))
            )
        );
    }

    /**
     * Scope a query to filter by order departure country ID.
     *
     * @param int $country
     */
    protected function scopeBidOrderDepartureCountry(QueryBuilder $builder, $country)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->orders_table_alias}.ship_from_country",
                $builder->createNamedParameter((int) $country, ParameterType::INTEGER, $this->nameScopeParameter('bidOrderDepartureCountryId'))
            )
        );
    }

    /**
     * Scope a query to filter by order departure region ID.
     *
     * @param int $region
     */
    protected function scopeBidOrderDepartureRegion(QueryBuilder $builder, $region)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->orders_table_alias}.ship_from_state",
                $builder->createNamedParameter((int) $region, ParameterType::INTEGER, $this->nameScopeParameter('bidOrderDepartureRegionId'))
            )
        );
    }

    /**
     * Scope a query to filter by order departure city ID.
     *
     * @param int $city
     */
    protected function scopeBidOrderDepartureCity(QueryBuilder $builder, $city)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->orders_table_alias}.ship_from_city",
                $builder->createNamedParameter((int) $city, ParameterType::INTEGER, $this->nameScopeParameter('bidOrderDepartureCityId'))
            )
        );
    }

    /**
     * Scope a query to filter by order destination country ID.
     *
     * @param int $country
     */
    protected function scopeBidOrderDestinationCountry(QueryBuilder $builder, $country)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->orders_table_alias}.ship_to_country",
                $builder->createNamedParameter((int) $country, ParameterType::INTEGER, $this->nameScopeParameter('bidOrderDestinationCountryId'))
            )
        );
    }

    /**
     * Scope a query to filter by order destination region ID.
     *
     * @param int $region
     */
    protected function scopeBidOrderDestinationRegion(QueryBuilder $builder, $region)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->orders_table_alias}.ship_to_state",
                $builder->createNamedParameter((int) $region, ParameterType::INTEGER, $this->nameScopeParameter('bidOrderDestinationRegionId'))
            )
        );
    }

    /**
     * Scope a query to filter by order destination city ID.
     *
     * @param int $city
     */
    protected function scopeBidOrderDestinationCity(QueryBuilder $builder, $city)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->orders_table_alias}.ship_to_city",
                $builder->createNamedParameter((int) $city, ParameterType::INTEGER, $this->nameScopeParameter('bidOrderDestinationCityId'))
            )
        );
    }

    /**
     * Scope a query to filter by bid shipper ID.
     *
     * @param int $shipper_id
     */
    protected function scopeBidShipper(QueryBuilder $builder, $shipper_id)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->shippers_bids_table_alias}.id_shipper",
                $builder->createNamedParameter((int) $shipper_id, ParameterType::INTEGER, $this->nameScopeParameter('bidShipperId'))
            )
        );
    }

    protected function scopeBidIdSeller(QueryBuilder $builder, $sellerId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->orders_table_alias}.id_seller",
                $builder->createNamedParameter((int) $sellerId, ParameterType::INTEGER, $this->nameScopeParameter('idSeller'))
            )
        );
    }

    protected function scopeBidIdBuyer(QueryBuilder $builder, $buyerId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->orders_table_alias}.id_buyer",
                $builder->createNamedParameter((int) $buyerId, ParameterType::INTEGER, $this->nameScopeParameter('idBuyer'))
            )
        );
    }

    /**
     * Scope a query to filter by bid status.
     *
     * @param string $status
     */
    protected function scopeBidStatus(QueryBuilder $builder, $status)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->shippers_bids_table_alias}.quote_status",
                $builder->createNamedParameter($status, ParameterType::STRING, $this->nameScopeParameter('bidStatus'))
            )
        );
    }

    /**
     * Scope a query to filter by creation datetime.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeBidCreatedAt(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->shippers_bids_table_alias}.create_date",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('bidCreatedAt'))
            )
        );
    }

    /**
     * Scope a query to filter by creation datetime from.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeBidCreatedFrom(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "{$this->shippers_bids_table_alias}.create_date",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('bidCreatedFrom'))
            )
        );
    }

    /**
     * Scope a query to filter by creation datetime to.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeBidCreatedTo(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "{$this->shippers_bids_table_alias}.create_date",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('bidCreatedTo'))
            )
        );
    }

    /**
     * Scope a query to filter by creation date.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeBidCreatedAtDate(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "DATE({$this->shippers_bids_table_alias}.create_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('bidCreatedAtDate'))
            )
        );
    }

    /**
     * Scope a query to filter by creation date from.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeBidCreatedFromDate(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->shippers_bids_table_alias}.create_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('bidCreatedFromDate'))
            )
        );
    }

    /**
     * Scope a query to filter by creation date to.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeBidCreatedToDate(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->shippers_bids_table_alias}.create_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('bidCreatedToDate'))
            )
        );
    }

    /**
     * Scope a query to filter by update datetime.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeBidUpdatedAt(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->shippers_bids_table_alias}.update_date",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('bidUpdatedAt'))
            )
        );
    }

    /**
     * Scope a query to filter by update datetime from.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeBidUpdatedFrom(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "{$this->shippers_bids_table_alias}.update_date",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('bidUpdatedFrom'))
            )
        );
    }

    /**
     * Scope a query to filter by update datetime to.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeBidUpdatedTo(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "{$this->shippers_bids_table_alias}.update_date",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('bidUpdatedTo'))
            )
        );
    }

    /**
     * Scope a query to filter by update date.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeBidUpdatedAtDate(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "DATE({$this->shippers_bids_table_alias}.update_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('bidUpdatedAtDate'))
            )
        );
    }

    /**
     * Scope a query to filter by update date from.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeBidUpdatedFromDate(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->shippers_bids_table_alias}.update_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('bidUpdatedFromDate'))
            )
        );
    }

    /**
     * Scope a query to filter by update date to.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeBidUpdatedToDate(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->shippers_bids_table_alias}.update_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('bidUpdatedToDate'))
            )
        );
    }

    /**
     * Scope a query to filter by pickup datetime.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeBidPickupAt(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->shippers_bids_table_alias}.pickup_date",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('bidPickupAt'))
            )
        );
    }

    /**
     * Scope a query to filter by pickup datetime from.
     *
     * @param \DateTimeInterface|int|string $pickup_at
     */
    protected function scopeBidPickupFrom(QueryBuilder $builder, $pickup_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($pickup_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "{$this->shippers_bids_table_alias}.pickup_date",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('bidPickupFrom'))
            )
        );
    }

    /**
     * Scope a query to filter by pickup datetime to.
     *
     * @param \DateTimeInterface|int|string $pickup_at
     */
    protected function scopeBidPickupTo(QueryBuilder $builder, $pickup_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($pickup_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "{$this->shippers_bids_table_alias}.pickup_date",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('bidPickupTo'))
            )
        );
    }

    /**
     * Scope a query to filter by pickup date.
     *
     * @param \DateTimeInterface|int|string $pickup_at
     */
    protected function scopeBidPickupAtDate(QueryBuilder $builder, $pickup_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($pickup_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "DATE({$this->shippers_bids_table_alias}.pickup_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('bidPickupAtDate'))
            )
        );
    }

    /**
     * Scope a query to filter by pickup date from.
     *
     * @param \DateTimeInterface|int|string $pickup_at
     */
    protected function scopeBidPickupFromDate(QueryBuilder $builder, $pickup_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($pickup_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->shippers_bids_table_alias}.pickup_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('bidPickupFromDate'))
            )
        );
    }

    /**
     * Scope a query to filter by pickup date to.
     *
     * @param \DateTimeInterface|int|string $pickup_at
     */
    protected function scopeBidPickupToDate(QueryBuilder $builder, $pickup_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($pickup_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->shippers_bids_table_alias}.pickup_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('bidPickupToDate'))
            )
        );
    }

    /**
     * Scope a query to filter by pickup datetime.
     *
     * @param \DateTimeInterface|int|string $delivered_at
     */
    protected function scopeBidDeliveryAt(QueryBuilder $builder, $delivered_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($delivered_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->shippers_bids_table_alias}.delivery_date",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('bidDeliveredAt'))
            )
        );
    }

    /**
     * Scope a query to filter by pickup datetime from.
     *
     * @param \DateTimeInterface|int|string $delivered_at
     */
    protected function scopeBidDeliveryFrom(QueryBuilder $builder, $delivered_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($delivered_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "{$this->shippers_bids_table_alias}.delivery_date",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('bidDeliveredFrom'))
            )
        );
    }

    /**
     * Scope a query to filter by pickup datetime to.
     *
     * @param \DateTimeInterface|int|string $delivered_at
     * @param mixed                         $pickup_at
     */
    protected function scopeBidDeliveryTo(QueryBuilder $builder, $pickup_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($pickup_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "{$this->shippers_bids_table_alias}.delivery_date",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('bidDeliveredTo'))
            )
        );
    }

    /**
     * Scope a query to filter by pickup date.
     *
     * @param \DateTimeInterface|int|string $delivered_at
     */
    protected function scopeBidDeliveryAtDate(QueryBuilder $builder, $delivered_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($delivered_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "DATE({$this->shippers_bids_table_alias}.delivery_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('bidDeliveredAtDate'))
            )
        );
    }

    /**
     * Scope a query to filter by pickup date from.
     *
     * @param \DateTimeInterface|int|string $delivered_at
     */
    protected function scopeBidDeliveryFromDate(QueryBuilder $builder, $delivered_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($delivered_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->shippers_bids_table_alias}.delivery_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('bidDeliveredFromDate'))
            )
        );
    }

    /**
     * Scope a query to filter by pickup date to.
     *
     * @param \DateTimeInterface|int|string $delivered_at
     */
    protected function scopeBidDeliveryToDate(QueryBuilder $builder, $delivered_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($delivered_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->shippers_bids_table_alias}.delivery_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('bidDeliveredToDate'))
            )
        );
    }

    /**
     * Resolves static relationships with order.
     */
    protected function bidOrder(): RelationInterface
    {
        return $this->belongsTo(
            new PortableModel($this->getHandler(), $this->orders_table, 'id'),
            'id_order'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with bidder.
     */
    protected function bidUser(): RelationInterface
    {
        return $this->belongsTo(
            new PortableModel($this->getHandler(), $this->users_table, 'idu'),
            'id_shipper'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with bid' shipper.
     */
    protected function bidShipper(): RelationInterface
    {
        return $this->belongsTo(
            new PortableModel($this->getHandler(), $this->shippers_table, 'id'),
            'id_shipper',
            'id_user'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with bid' shipper.
     */
    protected function bidShippingType(): RelationInterface
    {
        return $this->belongsTo(
            new PortableModel($this->getHandler(), $this->shipping_types_table, 'id'),
            'id_shipping_type',
            'id_type'
        )->disableNativeCast();
    }

    /**
     * Creates new related instance of the model for relation.
     *
     * @param BaseModel|Model|string $source
     */
    protected function resolveRelatedModel($source): Model
    {
        if ($source === $this) {
            return new PortableModel($this->getHandler(), $this->shippers_bids_table_alias, 'id_quote');
        }

        return parent::resolveRelatedModel($source);
    }
}

// End of file orders_quotes_model.php
// Location: /tinymvc/myapp/models/orders_quotes_model.php
