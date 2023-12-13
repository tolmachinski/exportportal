<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Types\Types as CustomTypes;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * User_Basket model
 */
final class User_Basket_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "user_basket";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "USER_BASKET";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id_basket_item";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_basket_item'        => Types::INTEGER,
        'id_user'               => Types::INTEGER,
        'id_item'               => Types::INTEGER,
        'price_item'            => CustomTypes::SIMPLE_MONEY,
        'quantity'              => Types::INTEGER,
        'date'                  => Types::DATETIME_IMMUTABLE,
        'shipping_insurance'    => Types::INTEGER,
    ];

    /**
     * Scope query by user id
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
                "`{$this->getTable()}`.`id_user`",
                $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('userId'))
            )
        );
    }

    /**
     * Scope query by basket key
     *
     * @param QueryBuilder $builder
     * @param string $basketKey
     *
     * @return void
     */
    protected function scopeBasketKey(QueryBuilder $builder, string $basketKey): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`basket_item_key`",
                $builder->createNamedParameter($basketKey, ParameterType::STRING, $this->nameScopeParameter('basketKey'))
            )
        );
    }
}

/* End of file user_basket_model.php */
/* Location: /tinymvc/myapp/models/user_basket_model.php */
