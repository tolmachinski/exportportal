<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Out_Of_Stock model
 */
final class Out_Of_Stock_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "notify_out_of_stock";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "NOTIFY_OUT_OF_STOCK";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'            => Types::INTEGER,
        'id_user'       => Types::INTEGER,
        'id_item'       => Types::INTEGER,
        'was_notified'  => Types::INTEGER,
        'date_notified' => Types::DATETIME_IMMUTABLE,
    ];

    /**
     * Scope a query by the user id
     *
     * @param QueryBuilder $builder
     * @param int $userId
     *
     * @return void
     */
    protected function scopeUserId(QueryBuilder $builder, int $userId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`id_user`",
                $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('userId'))
            )
        );
    }

    /**
     * Scope a query by the item id
     *
     * @param QueryBuilder $builder
     * @param int $itemId
     *
     * @return void
     */
    protected function scopeItemId(QueryBuilder $builder, int $itemId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`id_item`",
                $builder->createNamedParameter($itemId, ParameterType::INTEGER, $this->nameScopeParameter('itemId'))
            )
        );
    }

    /**
     * Scope a query by the item id
     *
     * @param QueryBuilder $builder
     * @param int $wasNotified
     *
     * @return void
     */
    protected function scopeWasNotified(QueryBuilder $builder, int $wasNotified): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`was_notified`",
                $builder->createNamedParameter($wasNotified, ParameterType::INTEGER, $this->nameScopeParameter('wasNotified'))
            )
        );
    }
}

/* End of file out_of_stock_model.php */
/* Location: /tinymvc/myapp/models/out_of_stock_model.php */
