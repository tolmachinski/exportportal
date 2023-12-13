<?php

declare(strict_types=1);

use App\Common\Contracts\Product\ProductDescriptionStatus;

use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;

/**
 * Product_Descriptions model
 */
final class Product_Descriptions_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "items_descriptions";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "ITEMS_DESCRIPTIONS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id_items_descriptions";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_items_descriptions'     => Types::INTEGER,
        'id_item'                   => Types::INTEGER,
        'descriptions_lang'         => Types::INTEGER,
        'create_date'               => Types::DATETIME_IMMUTABLE,
        'update_date'               => Types::DATETIME_IMMUTABLE,
        'need_translate'            => Types::INTEGER,
        'status'                    => ProductDescriptionStatus::class,
    ];
}

/* End of file product_descriptions_model.php */
/* Location: /tinymvc/myapp/models/product_descriptions_model.php */
