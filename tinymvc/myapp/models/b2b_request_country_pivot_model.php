<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * B2b_Request_Country_Pivot model.
 */
final class B2b_Request_Country_Pivot_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'b2b_request_relation_countries';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'B2B_REQUEST_RELATION_COUNTRIES';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'          => Types::INTEGER,
        'request_id'  => Types::INTEGER,
        'country_id'  => Types::INTEGER,
    ];

    /**
     * Scope a query to filter by request ID.
     */
    protected function scopeIdRequest(QueryBuilder $builder, int $requestId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('request_id'),
                $builder->createNamedParameter($requestId, ParameterType::INTEGER, $this->nameScopeParameter('requestId'))
            )
        );
    }

    /**
     * Scope a query to filter by country ID.
     */
    protected function scopeIdCountry(QueryBuilder $builder, int $countryId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('country_id'),
                $builder->createNamedParameter($countryId, ParameterType::INTEGER, $this->nameScopeParameter('countryId'))
            )
        );
    }

    /**
     * Scope for join with countries.
     */
    protected function bindCountries(QueryBuilder $builder): void
    {
        /** @var Countries_Model $portCountryModel */
        $portCountryModel = model(Countries_Model::class);
        $countryTable = $portCountryModel->getTable();

        $builder->leftJoin(
            $this->getTable(),
            $countryTable,
            $countryTable,
            "`{$countryTable}`.`{$portCountryModel->getPrimaryKey()}` = `{$this->getTable()}`.`country_id`"
        );
    }

    /**
     * Scope for join with countries.
     */
    protected function bindActiveRequests(QueryBuilder $builder): void
    {
        /** @var B2b_Requests_Model $requestsModel */
        $requestsModel = model(B2b_Requests_Model::class);
        $requestsTable = $requestsModel->getTable();

        /** @var Seller_Companies_Model $companyModel */
        $companyModel = model(Seller_Companies_Model::class);
        $companyTable = $companyModel->getTable();

        $builder
            ->andWhere("{$requestsTable}.`blocked` = 0")
            ->andWhere("{$requestsTable}.`status` = 'enabled'")
            ->andWhere("{$requestsTable}.`b2b_active` = 1")
            ->andWhere("{$companyTable}.`visible_company` = 1")
            ->andWhere("{$companyTable}.`blocked` = 0")
            ->leftJoin(
                $this->getTable(),
                $requestsTable,
                $requestsTable,
                "`{$requestsTable}`.`{$requestsModel->getPrimaryKey()}` = `{$this->getTable()}`.`request_id`"
            )->leftJoin(
                $requestsTable,
                $companyTable,
                $companyTable,
                "`{$companyTable}`.`{$companyModel->getPrimaryKey()}` = `{$requestsTable}`.`id_company`"
        );
    }
}

// End of file b2b_request_industry_pivot_model_model.php
// Location: /tinymvc/myapp/models/b2b_request_industry_pivot_model_model.php
