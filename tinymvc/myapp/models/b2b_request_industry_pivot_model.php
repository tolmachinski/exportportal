<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use App\Common\Database\Relations\RelationInterface;

/**
 * B2b_Request_Industry_Pivot model.
 */
final class B2b_Request_Industry_Pivot_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'b2b_request_relation_industry';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'B2B_REQUEST_RELATION_INDUSTRY';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_relation';

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_relation' => Types::INTEGER,
        'id_request'  => Types::INTEGER,
        'id_industy'  => Types::INTEGER,
    ];

    /**
     * Scope a query to filter by request ID.
     */
    protected function scopeIdRequest(QueryBuilder $builder, int $requestId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_request'),
                $builder->createNamedParameter($requestId, ParameterType::INTEGER, $this->nameScopeParameter('requestId'))
            )
        );
    }

    /**
     * Scope a query to filter by industry ID.
     */
    protected function scopeIdIndustry(QueryBuilder $builder, int $industryId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_industry'),
                $builder->createNamedParameter($industryId, ParameterType::INTEGER, $this->nameScopeParameter('industryId'))
            )
        );
    }

    /**
     * Scope for join with industries.
     */
    protected function bindIndustries(QueryBuilder $builder): void
    {
        /** @var Categories_Model $categoriesModel */
        $categoriesModel = model(Categories_Model::class);

        $builder
            ->leftJoin(
                $this->getTable(),
                $categoriesModel->getTable(),
                $categoriesModel->getTable(),
                "`{$categoriesModel->getTable()}`.`{$categoriesModel->getPrimaryKey()}` = `{$this->getTable()}`.`id_industry`"
            )
        ;
    }
}

// End of file b2b_request_industry_pivot_model_model.php
// Location: /tinymvc/myapp/models/b2b_request_industry_pivot_model_model.php
