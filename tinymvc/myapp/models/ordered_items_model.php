<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Query\QueryBuilder;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Database\Types\Types as CustomTypes;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\ParameterType;

/**
 * Ordered_Items model
 */
final class Ordered_Items_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "item_ordered";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "ITEM_ORDERED";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id_ordered_item";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_ordered_item'       => Types::INTEGER,
        'id_order'              => Types::INTEGER,
        'id_item'               => Types::INTEGER,
        'id_snapshot'           => Types::INTEGER,
        'price_ordered'         => CustomTypes::SIMPLE_MONEY,
        'quantity_ordered'      => Types::INTEGER,
        'weight_ordered'        => Types::FLOAT,
        'insurance_shipping'    => Types::INTEGER,
        'date_ordered'          => Types::DATETIME_IMMUTABLE,
    ];

    /**
     * Scope ordered items by order id
     *
     * @param QueryBuilder $builder
     * @param int $orderId
     * @return void
     */
    protected function scopeOrderId(QueryBuilder $builder, int $orderId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`id_order`",
                $builder->createNamedParameter($orderId, ParameterType::INTEGER, $this->nameScopeParameter('orderId'))
            )
        );
    }

    /**
     * Resolves static relationships with order status
     *
     * @return RelationInterface
     */
    protected function snapshot(): RelationInterface
    {
        return $this->belongsTo(Item_Snapshots_Model::class, 'id_snapshot', 'id_snapshot');
    }

}

/* End of file ordered_items_model.php */
/* Location: /tinymvc/myapp/models/ordered_items_model.php */
