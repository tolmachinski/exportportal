<?php

declare(strict_types=1);

use App\Common\Contracts\Bill\BillStatus;
use App\Common\Database\Model;
use Doctrine\DBAL\Query\QueryBuilder;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Database\Types\Types as CustomTypes;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\ParameterType;

/**
 * Bills model
 */
final class Bills_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "users_bills";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "USERS_BILLS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id_bill";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_bill'                   => Types::INTEGER,
        'id_user'                   => Types::INTEGER,
        'id_type_bill'              => Types::INTEGER,
        'id_item'                   => Types::INTEGER,
        'id_invoice'                => Types::INTEGER,
        'balance'                   => CustomTypes::SIMPLE_MONEY,
        'amount'                    => CustomTypes::SIMPLE_MONEY,
        'total_balance'             => CustomTypes::SIMPLE_MONEY,
        'pay_percents'              => Types::INTEGER,
        'pay_method'                => Types::INTEGER,
        'pay_date'                  => Types::DATETIME_IMMUTABLE,
        'create_date'               => Types::DATETIME_IMMUTABLE,
        'due_date'                  => Types::DATETIME_IMMUTABLE,
        'confirmed_date'            => Types::DATETIME_IMMUTABLE,
        'declined_date'             => Types::DATETIME_IMMUTABLE,
        'change_date'               => Types::DATETIME_IMMUTABLE,
        'note'                      => CustomTypes::SIMPLE_JSON_ARRAY,
        'refund_bill_request'       => Types::INTEGER,
        'extend_request'            => Types::INTEGER,
    ];

    /**
     * Scope user bills by item ID
     *
     * @param QueryBuilder $builder
     * @param int $itemId
     * @return void
     */
    protected function scopeItemId(QueryBuilder $builder, int $itemId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`id_item`",
                $builder->createNamedParameter($itemId, ParameterType::INTEGER, $this->nameScopeParameter('itemId'))
            )
        );
    }

    /**
     * Scope user bills by item IDs
     *
     * @param QueryBuilder $builder
     * @param array $itemIds
     * @return void
     */
    protected function scopeItemIds(QueryBuilder $builder, array $itemIds): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->table}`.`id_item`",
                array_map(
                    fn ($index, $itemId) => $builder->createNamedParameter(
                        (int) $itemId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("itemId_{$index}")
                    ),
                    array_keys($itemIds),
                    $itemIds
                )
            )
        );
    }

    /**
     * Scope user bills by bill status
     *
     * @param QueryBuilder $builder
     * @param BillStatus $billStatus
     * @return void
     */
    protected function scopeStatus(QueryBuilder $builder, BillStatus $billStatus): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`status`",
                $builder->createNamedParameter((string) $billStatus, ParameterType::STRING, $this->nameScopeParameter('billStatus'))
            )
        );
    }

    /**
     * Scope user bills by bill type id
     *
     * @param QueryBuilder $builder
     * @param int $billTypeId
     * @return void
     */
    protected function scopeType(QueryBuilder $builder, int $billTypeId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`id_type_bill`",
                $builder->createNamedParameter($billTypeId, ParameterType::INTEGER, $this->nameScopeParameter('billType'))
            )
        );
    }

    /**
     * Scope user bills by type IDs
     *
     * @param QueryBuilder $builder
     * @param int[] $typeIds
     * @return void
     */
    protected function scopeTypeIds(QueryBuilder $builder, array $typeIds): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->getTable()}`.`id_type_bill`",
                array_map(
                    fn ($index, $billTypeId) => $builder->createNamedParameter(
                        (int) $billTypeId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("billTypeId_{$index}")
                    ),
                    array_keys($typeIds),
                    $typeIds
                )
            )
        );
    }

    /**
     * Resolves static relationships with order status
     *
     * @return RelationInterface
     */
    protected function type(): RelationInterface
    {
        return $this->belongsTo(Bill_Types_Model::class, 'id_type_bill', 'id_type');
    }
}

/* End of file bills_model.php */
/* Location: /tinymvc/myapp/models/bills_model.php */
