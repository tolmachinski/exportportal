<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Item_Compilation_Relation model
 */
final class Item_Compilation_Relation_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "items_compilation_relation_items";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "ITEMS_COMPILATION_RELATION_ITEMS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * Scope a query by the compilation id
     *
     * @param QueryBuilder $builder
     * @param int $compilationId
     *
     * @return void
     */
    protected function scopeCompilationId(QueryBuilder $builder, int $compilationId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`compilation_id`",
                $builder->createNamedParameter($compilationId, ParameterType::INTEGER, $this->nameScopeParameter('compilationId'))
            )
        );
    }
}

/* End of file item_compilation_relation_model.php */
/* Location: /tinymvc/myapp/models/item_compilation_relation_model.php */
