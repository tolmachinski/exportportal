<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\ParameterType;

/**
 * User_Saved_Items model
 */
final class User_Saved_Items_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "user_saved_items";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "USER_SAVED_ITEMS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id_save";

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
}

/* End of file user_saved_items_model.php */
/* Location: /tinymvc/myapp/models/user_saved_items_model.php */
