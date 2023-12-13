<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\ParameterType;

/**
 * Partners_Types model
 */
final class Partners_Types_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "partners_type";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "PARTNERS_TYPE";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id_type";


    /**
     * Scope a query to filter by type ID.
     */
    protected function scopeId(QueryBuilder $builder, int $typeId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn($this->getPrimaryKey()),
                $builder->createNamedParameter($typeId, ParameterType::INTEGER, $this->nameScopeParameter('typeId'))
            )
        );
    }
}

/* End of file partners_types_model.php */
/* Location: /tinymvc/myapp/models/partners_types_model.php */
