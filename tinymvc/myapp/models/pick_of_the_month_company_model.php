<?php

declare(strict_types=1);

use App\Common\Database\Model;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use App\Common\Database\Relations\RelationInterface;

/**
 * Pick_Of_The_Month_Company model
 */
final class Pick_Of_The_Month_Company_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "pick_of_the_month_company";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "PICK_OF_THE_MONTH_COMPANY";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * Scope a query to filter by creation date from.
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
     * Scope query by id.
     */
    protected function scopeIdCompany(QueryBuilder $builder, int $id): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_company',
                $builder->createNamedParameter($id, ParameterType::INTEGER, $this->nameScopeParameter('id_company'))
            )
        );
    }


    /**
     * Scope for join with companies.
     */
    protected function bindCompanies(QueryBuilder $builder): void
    {
        /** @var Seller_Companies_Model $companyModel */
        $companyModel = model(Seller_Companies_Model::class);
        $companyTable = $companyModel->getTable();

        $builder
            ->leftJoin(
                $this->getTable(),
                $companyTable,
                $companyTable,
                "`{$companyTable}`.`{$companyModel->getPrimaryKey()}` = `{$this->getTable()}`.`id_company`"
            )
        ;
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
     * Relation with the recipient.
     */
    protected function company(): RelationInterface
    {
        return $this->belongsTo(Seller_Companies_Model::class, 'id_company')->disableNativeCast();
    }
}

/* End of file pick_of_the_month_company_model.php */
/* Location: /tinymvc/myapp/models/pick_of_the_month_company_model.php */
