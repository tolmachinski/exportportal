<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Query\QueryBuilder;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\ParameterType;

/**
 * Product_Order_Comments model
 */
final class Product_Order_Comments_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "item_order_comments";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "ITEM_ORDER_COMMENTS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'            => Types::INTEGER,
        'order_id'      => Types::INTEGER,
        'user_id'       => Types::INTEGER,
        'create_date'  => Types::DATETIME_IMMUTABLE,
    ];

    /**
     * Scope order comments by order ID
     *
     * @param QueryBuilder $builder
     * @param int $orderId
     * @return void
     */
    protected function scopeOrderId(QueryBuilder $builder, int $orderId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`order_id`",
                $builder->createNamedParameter($orderId, ParameterType::INTEGER, $this->nameScopeParameter('orderId'))
            )
        );
    }

    /**
     * Resolves static relationships with comment author
     *
     * @return RelationInterface
     */
    protected function user(): RelationInterface
    {
        /** @var Users_Model $usersModel */
        $usersModel = model(Users_Model::class);
        $usersTable = $usersModel->getTable();

        $relation = $this->belongsTo(Users_Model::class, 'user_id', 'idu');
        $queryBuilder = $relation->getQuery();

        $queryBuilder->select(
            "`{$usersTable}`.`idu`",
            "`{$usersTable}`.`fname`",
            "`{$usersTable}`.`lname`",
            "`{$usersTable}`.`email`",
            "`{$usersTable}`.`status`",
        );

        return $relation;
    }
}

/* End of file product_order_comments_model.php */
/* Location: /tinymvc/myapp/models/product_order_comments_model.php */
