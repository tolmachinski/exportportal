<?php

declare(strict_types=1);

use App\Common\Database\Model;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use App\Common\Database\Relations\RelationInterface;

/**
 * Pick_Of_The_Month_Item model
 */
final class Pick_Of_The_Month_Item_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "pick_of_the_month_item";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "PICK_OF_THE_MONTH_ITEM";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * Scope query by id.
     */
    protected function scopeIdItem(QueryBuilder $builder, int $id): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_item',
                $builder->createNamedParameter($id, ParameterType::INTEGER, $this->nameScopeParameter('id_item'))
            )
        );
    }

    /**
     * Scope a query to by date range.
     *
     * @param \DateTimeInterface|int|string $date
     */
    protected function scopeDateBetween(QueryBuilder $builder, $date)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($date, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            sprintf(
                "%s BETWEEN `start_date` AND `end_date`",
                $builder->createNamedParameter(
                    $date,
                    ParameterType::STRING,
                    $this->nameScopeParameter('date')
                )
            )
        );
    }

    /**
     * Scope for join with users
     */
    protected function bindUsers(QueryBuilder $builder): void
    {
        /** @var Users_Model $userModel */
        $userModel = model(Users_Model::class);
        $userTable = $userModel->getTable();

        $builder
            ->leftJoin(
                $this->getTable(),
                $userTable,
                $userTable,
                "`{$userTable}`.`{$userModel->getPrimaryKey()}` = `{$this->getTable()}`.`id_seller`"
            );
    }

    /**
     * Scope for join with items
     */
    protected function bindItems(QueryBuilder $builder): void
    {
         /** @var Products_Model $productsModel */
         $productsModel = model(Products_Model::class);
         $productsModelTable = $productsModel->getTable();

         $builder
             ->leftJoin(
                 $this->getTable(),
                 $productsModelTable,
                 $productsModelTable,
                 "`{$productsModelTable}`.`{$productsModel->getPrimaryKey()}` = `{$this->getTable()}`.`id_item`"
             );
    }

    /**
     * Relation with the recipient.
     */
    protected function item(): RelationInterface
    {
        return $this->belongsTo(Products_Model::class, 'id_item')->disableNativeCast();
    }
}

/* End of file pick_of_the_month_item_model.php */
/* Location: /tinymvc/myapp/models/pick_of_the_month_item_model.php */
