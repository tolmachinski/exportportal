<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * B2b_Request_Category_Pivot model
 */
final class B2b_Request_Category_Pivot_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "b2b_request_relation_category";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "B2B_REQUEST_RELATION_CATEGORY";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id_relation";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_relation' => Types::INTEGER,
        'id_request'  => Types::INTEGER,
        'id_category' => Types::INTEGER,
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
     * Scope a query to filter by category ID.
     */
    protected function scopeIdIndustry(QueryBuilder $builder, int $categoryId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_category'),
                $builder->createNamedParameter($categoryId, ParameterType::INTEGER, $this->nameScopeParameter('categoryId'))
            )
        );
    }
}

/* End of file b2b_request_category_pivot_model.php */
/* Location: /tinymvc/myapp/models/b2b_request_category_pivot_model.php */
