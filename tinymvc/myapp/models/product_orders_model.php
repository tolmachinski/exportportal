<?php

declare(strict_types=1);

use App\Casts\Order\ProductOrderStateCast;
use App\Casts\Order\ProductOrderTypeCast;
use App\Casts\Shipper\ShipperTypeCast;
use App\Common\Contracts\Order\ProductOrderStatusAlias;
use App\Common\Contracts\Bill\BillTypes;
use App\Common\Database\Concerns;
use App\Common\Database\Model;
use Doctrine\DBAL\Query\QueryBuilder;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Database\Types\Types as CustomTypes;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\ParameterType;
use Money\Money;

/**
 * Product orders model.
 */
final class Product_Orders_Model extends Model
{
    use Concerns\CanSearch;

    /**
     * The name of the "created at" column.
     */
    protected const CREATED_AT = 'order_date';

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
    protected string $table = 'item_orders';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'PRODUCT_ORDERS';

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
        'shipping_quote_details',
        'shipper_type',
        'purchased_products',
        'purchase_order',
        'purchase_order_timeline',
        'po',
        'ishippers_quotes',
        'status_countdown',
        'delivery_date',
        'package_detail',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'                          => Types::INTEGER,
        'id_buyer'                    => Types::INTEGER,
        'id_seller'                   => Types::INTEGER,
        'id_shipper'                  => Types::INTEGER,
        'shipping_quote_details'      => Types::JSON,
        'shipper_type'                => ShipperTypeCast::class,
        'ep_manager'                  => Types::INTEGER,
        'id_invoice'                  => Types::INTEGER,
        'purchased_products'          => Types::JSON,
        'purchase_order'              => Types::JSON,
        'purchase_order'              => Types::JSON,
        'purchase_order_timeline'     => Types::JSON,
        'price'                       => CustomTypes::SIMPLE_MONEY,
        'discount'                    => Types::DECIMAL,
        'final_price'                 => CustomTypes::SIMPLE_MONEY,
        'status'                      => Types::INTEGER,
        'ship_price'                  => CustomTypes::SIMPLE_MONEY,
        'ishippers_quotes'            => Types::JSON,
        'ship_confirmed'              => Types::BOOLEAN,
        'ship_from_country'           => Types::INTEGER,
        'ship_from_state'             => Types::INTEGER,
        'ship_from_city'              => Types::INTEGER,
        'ship_to_country'             => Types::INTEGER,
        'ship_to_state'               => Types::INTEGER,
        'ship_to_city'                => Types::INTEGER,
        'seller_delivery_area'        => Types::INTEGER,
        'shipment_type'               => Types::INTEGER,
        'shipping_insurance_accepted' => Types::BOOLEAN,
        'shipping_insurance_details'  => Types::JSON,
        'weight'                      => Types::DECIMAL,
        'status_countdown'            => Types::DATETIME_IMMUTABLE,
        'status_countdown_updated'    => Types::DATETIME_IMMUTABLE,
        'last_extend'                 => Types::BOOLEAN,
        'extend_request'              => Types::INTEGER,
        'update_date'                 => Types::DATETIME_IMMUTABLE,
        'state'                       => ProductOrderStateCast::class,
        'order_date'                  => Types::DATETIME_IMMUTABLE,
        'delivery_date'               => Types::DATE_IMMUTABLE,
        'order_summary'               => CustomTypes::SIMPLE_JSON_ARRAY,
        'order_type'                  => ProductOrderTypeCast::class,
        'id_by_type'                  => Types::INTEGER,
        'state_seller'                => Types::INTEGER,
        'state_buyer'                 => Types::INTEGER,
        'timeline_countdowns'         => Types::JSON,
        'cancel_request'              => Types::INTEGER,
        'dispute_opened'              => Types::INTEGER,
        'external_bills'              => CustomTypes::SIMPLE_JSON_ARRAY,
        'package_detail'              => Types::JSON,
        'quote_requested'             => Types::BOOLEAN,
        'reminder_sent'               => Types::BOOLEAN,
        'auto_extend'                 => Types::BOOLEAN,
        'request_auto_extend'         => Types::BOOLEAN,
        'shipper_confirm_delivery'    => Types::BOOLEAN,
    ];

    /**
     * Scope order by order status
     *
     * @param QueryBuilder $builder
     * @param int $orderStatus
     * @return void
     */
    protected function scopeOrderStatus(QueryBuilder $builder, int $orderStatus): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`status`",
                $builder->createNamedParameter($orderStatus, ParameterType::INTEGER, $this->nameScopeParameter('orderStatus'))
            )
        );
    }

    /**
     * Scope order by is dispute opened or not
     *
     * @param QueryBuilder $builder
     * @param int $isDisputeOpened
     * @return void
     */
    protected function scopeIsDisputeOpened(QueryBuilder $builder, int $isDisputeOpened): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`dispute_opened`",
                $builder->createNamedParameter($isDisputeOpened, ParameterType::INTEGER, $this->nameScopeParameter('isDisputeOpened'))
            )
        );
    }

    /**
     * Scope order by is cancel request opened or not
     *
     * @param QueryBuilder $builder
     * @param int $isCancelRequestOpened
     * @return void
     */
    protected function scopeIsCancelRequestOpened(QueryBuilder $builder, int $isCancelRequestOpened): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`cancel_request`",
                $builder->createNamedParameter($isCancelRequestOpened, ParameterType::INTEGER, $this->nameScopeParameter('isCancelRequestOpened'))
            )
        );
    }

    /**
     * Scope order by keywords
     *
     * @param QueryBuilder $builder
     * @param string $keywords
     * @return void
     */
    protected function scopeKeywords(QueryBuilder $builder, string $keywords): void
    {
        if (empty($keywords)) {
            return;
        }

        $this->appendSearchConditionsToQuery(
            $builder,
            $keywords,
            ['search_info'],
            ['search_info'],
        );
    }

    /**
     * Scope order by order date from
     *
     * @param QueryBuilder $builder
     * @param string $orderDate - Y-m-d
     * @return void
     */
    protected function scopeOrderCreateDateGte(QueryBuilder $builder, string $orderDate): void
    {
        $builder->andWhere(
            $builder->expr()->gte(
                "DATE(`{$this->getTable()}`.`order_date`)",
                $builder->createNamedParameter($orderDate, ParameterType::STRING, $this->nameScopeParameter('orderCreateDateGte'))
            )
        );
    }

    /**
     * Scope order by order date to
     *
     * @param QueryBuilder $builder
     * @param string $orderDate - Y-m-d
     * @return void
     */
    protected function scopeOrderCreateDateLte(QueryBuilder $builder, string $orderDate): void
    {
        $builder->andWhere(
            $builder->expr()->lte(
                "DATE(`{$this->getTable()}`.`order_date`)",
                $builder->createNamedParameter($orderDate, ParameterType::STRING, $this->nameScopeParameter('orderCreateDateLte'))
            )
        );
    }

    /**
     * Scope order by final price from
     *
     * @param QueryBuilder $builder
     * @param Money $finalPrice
     * @return void
     */
    protected function scopeFinalPriceGte(QueryBuilder $builder, Money $finalPrice): void
    {
        $builder->andWhere(
            $builder->expr()->gte(
                "`{$this->getTable()}`.`final_price`",
                $builder->createNamedParameter(\moneyToDecimal($finalPrice), ParameterType::STRING, $this->nameScopeParameter('finalPriceFrom'))
            )
        );
    }

    /**
     * Scope order by final price to
     *
     * @param QueryBuilder $builder
     * @param Money $finalPrice
     * @return void
     */
    protected function scopeFinalPriceLte(QueryBuilder $builder, Money $finalPrice): void
    {
        $builder->andWhere(
            $builder->expr()->lte(
                "`{$this->getTable()}`.`final_price`",
                $builder->createNamedParameter(\moneyToDecimal($finalPrice), ParameterType::STRING, $this->nameScopeParameter('finalPriceTo'))
            )
        );
    }

    /**
     * Scope order by status group
     *
     * @param QueryBuilder $builder
     * @param string $statusGroup ['new', 'active', 'passed']
     * @return void
     */
    protected function scopeOrderStatusGroup(QueryBuilder $builder, string $statusGroup): void
    {
        if (empty($orderStatusAliases = ProductOrderStatusAlias::getGroupStatuses($statusGroup))) {
            return;
        }

        /** @var Product_Orders_Statuses_Model $productOrdersStatusesModel */
        $productOrdersStatusesModel = model(Product_Orders_Statuses_Model::class);

        $builder->andWhere(
            $builder->expr()->in(
                "`{$productOrdersStatusesModel->getTable()}`.`alias`",
                array_map(
                    fn ($index, $statusAlias) => $builder->createNamedParameter(
                        (string) $statusAlias,
                        ParameterType::STRING,
                        $this->nameScopeParameter("statusAlias_{$index}")
                    ),
                    array_keys($orderStatusAliases),
                    $orderStatusAliases
                )
            )
        );
    }

    /**
     * Scope order by users demo or not
     *
     * @param QueryBuilder $builder
     * @param int $onlyFromRealUsers
     * @return void
     */
    protected function scopeRealUsers(QueryBuilder $builder, int $onlyFromRealUsers): void
    {
        /** @var Users_Model $usersModel */
        $usersModel = model(Users_Model::class);

        $usersTable = $usersModel->getTable();
        $productOrdersTable = $this->getTable();

        $builder
            ->leftJoin(
                $productOrdersTable,
                $usersTable,
                'orderBuyers',
                "`orderBuyers`.`idu` = `{$productOrdersTable}`.`id_buyer`"
            )
            ->leftJoin(
                $productOrdersTable,
                $usersTable,
                'orderSellers',
                "`orderSellers`.`idu` = `{$productOrdersTable}`.`id_seller`"
            );

        if ($onlyFromRealUsers) {
            $builder->andWhere(
                $builder->expr()->eq("`orderBuyers`.`fake_user`", 0),
                $builder->expr()->eq("`orderBuyers`.`is_model`", 0),
                $builder->expr()->eq("`orderSellers`.`fake_user`", 0),
                $builder->expr()->eq("`orderSellers`.`is_model`", 0)
            );
        } else {
            $builder->andWhere(
                $builder->expr()->or(
                    $builder->expr()->eq("`orderBuyers`.`fake_user`", 1),
                    $builder->expr()->eq("`orderBuyers`.`is_model`", 1),
                    $builder->expr()->eq("`orderSellers`.`fake_user`", 1),
                    $builder->expr()->eq("`orderSellers`.`is_model`", 1)
                )
            );
        }
    }

    /**
     * Scope order by assigned manager email
     *
     * @param QueryBuilder $builder
     * @param string $managerEmail
     * @return void
     */
    protected function scopeAssignedManagerEmail(QueryBuilder $builder, string $managerEmail): void
    {
        /** @var Users_Model $usersModel */
        $usersModel = model(Users_Model::class);

        $usersTable = $usersModel->getTable();
        $productOrdersTable = $this->getTable();

        $builder
            ->leftJoin(
                $productOrdersTable,
                $usersTable,
                'orderManager',
                "`orderManager`.`idu` = `{$productOrdersTable}`.`ep_manager`"
            )
            ->andWhere(
                $builder->expr()->eq(
                    "`orderManager`.`email`",
                    $builder->createNamedParameter($managerEmail, ParameterType::STRING, $this->nameScopeParameter('assignedManagerEmail'))
                )
            );
    }

    /**
     * Resolves static relationships with order status
     *
     * @return RelationInterface
     */
    protected function orderStatus(): RelationInterface
    {
        /** @var Product_Orders_Statuses_Model $productOrdersStatusesModel */
        $productOrdersStatusesModel = model(Product_Orders_Statuses_Model::class);
        $productOrdersStatusesTable = $productOrdersStatusesModel->getTable();

        /** @var RelationInterface $relation */
        $relation = $this->belongsTo(Product_Orders_Statuses_Model::class, 'status', 'id');
        $queryBuilder = $relation->getQuery();
        $queryBuilder->select(
            "`{$productOrdersStatusesTable}`.`id`",
            "`{$productOrdersStatusesTable}`.`status`",
            "`{$productOrdersStatusesTable}`.`alias`",
            "`{$productOrdersStatusesTable}`.`icon`",
            "`{$productOrdersStatusesTable}`.`icon_new`"
        );

        return $relation;
    }

    /**
     * Resolves static relationships with order status
     *
     * @return RelationInterface
     */
    protected function orderBills(): RelationInterface
    {
        /** @var Bills_Model $billsModel */
        $billsModel = model(Bills_Model::class);
        $billsTable = $billsModel->getTable();

        /** @var Bill_Types_Model $billTypesModel */
        $billTypesModel = model(Bill_Types_Model::class);
        $billTypesTable = $billTypesModel->getTable();

        /** @var RelationInterface $relation */
        $relation = $this->hasMany(Bills_Model::class, 'id_item', 'id');

        $queryBuilder = $relation->getQuery();

        $queryBuilder
            ->select(
                "`{$billsTable}`.*",
                "`{$billTypesTable}`.`name_type`",
                "`{$billTypesTable}`.`show_name`",
            )
            ->leftJoin(
                $billsTable,
                $billTypesTable,
                $billTypesTable,
                "`{$billsTable}`.`id_type_bill` = `{$billTypesTable}`.`id_type`"
            )
            ->andWhere(
                $queryBuilder->expr()->in(
                    "`{$billsModel->getTable()}`.`id_type_bill`",
                    [
                        BillTypes::getId(BillTypes::ORDER()),
                        BillTypes::getId(BillTypes::SHIPPING()),
                    ]
                )
            );

        return $relation;
    }

    /**
     * Scope for join with order status
     *
     * @param QueryBuilder $builder
     * @return void
     */
    protected function bindOrderStatus(QueryBuilder $builder): void
    {
        /** @var Product_Orders_Statuses_Model $productOrdersStatusesModel */
        $productOrdersStatusesModel = model(Product_Orders_Statuses_Model::class);
        $productOrdersStatusesTable = $productOrdersStatusesModel->getTable();

        $productOrdersTable = $this->getTable();

        $builder
            ->leftJoin(
                $productOrdersTable,
                $productOrdersStatusesTable,
                $productOrdersStatusesTable,
                "`{$productOrdersStatusesTable}`.`id` = `{$productOrdersTable}`.`status`"
            )
        ;
    }
}

// End of file item_orders_model.php
// Location: /tinymvc/myapp/models/item_orders_model.php
