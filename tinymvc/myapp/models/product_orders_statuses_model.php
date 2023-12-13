<?php

declare(strict_types=1);

use App\Casts\Order\ProductOrderStatusAliasCast;
use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;

/**
 * Product_Orders_Statuses model
 */
final class Product_Orders_Statuses_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "orders_status";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "ORDERS_STATUS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'                => Types::INTEGER,
        'role'              => Types::INTEGER,
        'countdown'         => Types::INTEGER,
        'alias'             => ProductOrderStatusAliasCast::class,
        'position'          => Types::INTEGER,
        'shipper_status'    => Types::INTEGER,
    ];
}

/* End of file product_orders_statuses_model.php */
/* Location: /tinymvc/myapp/models/product_orders_statuses_model.php */
