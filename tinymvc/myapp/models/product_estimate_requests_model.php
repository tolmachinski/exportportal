<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Types\Types as CustomTypes;

use Doctrine\DBAL\Types\Types;

/**
 * Product_Estimate_Requests model
 */
final class Product_Estimate_Requests_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "item_request_estimate";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "ITEM_REQUEST_ESTIMATE";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id_request_estimate";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_request_estimate'   => Types::INTEGER,
        'state_seller'          => Types::INTEGER,
        'update_date'           => Types::DATETIME_IMMUTABLE,
        'create_date'           => Types::DATETIME_IMMUTABLE,
        'expire_date'           => Types::DATETIME_IMMUTABLE,
        'state_buyer'           => Types::INTEGER,
        'id_seller'             => Types::INTEGER,
        'quantity'              => Types::INTEGER,
        'id_buyer'              => Types::INTEGER,
        'id_item'               => Types::INTEGER,
        'price'                 => CustomTypes::SIMPLE_MONEY,
        'days'                  => Types::INTEGER,
        'log'                   => Types::JSON,
    ];
}

/* End of file product_estimate_requests_model.php */
/* Location: /tinymvc/myapp/models/product_estimate_requests_model.php */
