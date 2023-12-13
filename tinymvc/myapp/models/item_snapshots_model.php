<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Types\Types as CustomTypes;
use Doctrine\DBAL\Types\Types;

/**
 * Item_Snapshots model
 */
final class Item_Snapshots_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "item_snapshots";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "ITEM_SNAPSHOTS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id_snapshot";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_snapshot'               => Types::INTEGER,
        'id_item'                   => Types::INTEGER,
        'id_seller'                 => Types::INTEGER,
        'additional_id'             => Types::INTEGER,
        'price'                     => CustomTypes::SIMPLE_MONEY,
        'item_weight'               => Types::FLOAT,
        'item_length'               => Types::FLOAT,
        'item_width'                => Types::FLOAT,
        'item_height'               => Types::FLOAT,
        'discount'                  => Types::INTEGER,
        'date_created'              => Types::DATETIME_IMMUTABLE,
        'snapshot_reviews_count'    => Types::INTEGER,
        'snapshot_rating'           => Types::FLOAT,
        'is_last_snapshot'          => Types::INTEGER,
    ];
}

/* End of file item_snapshots_model.php */
/* Location: /tinymvc/myapp/models/item_snapshots_model.php */
