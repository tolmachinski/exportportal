<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Item_Category model
 */
final class Item_Category_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "item_category";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "ITEM_CATEGORY";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "category_id";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'category_id'   => Types::INTEGER,
        'industry_id'   => Types::INTEGER,
        'parent'        => Types::INTEGER,
    ];

    /**
     * Scope query by category parent
     *
     * @param QueryBuilder $builder
     * @param int $parent
     *
     * @return void
     */
    protected function scopeParent(QueryBuilder $builder, int $parent): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`parent`",
                $builder->createNamedParameter($parent, ParameterType::INTEGER, $this->nameScopeParameter('categoryParent'))
            )
        );
    }

    /**
     * Scope query by the list of IDs.
     */
    protected function scopeIds(QueryBuilder $builder, array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                $this->getPrimaryKey(),
                array_map(
                    fn (int $index, $hashe) => $builder->createNamedParameter(
                        (int) $hashe,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("idFromList{$index}")
                    ),
                    array_keys($ids),
                    $ids
                )
            )
        );
    }

    /**
     * Scope query by the list of parent IDs.
     */
    protected function scopeParentIds(QueryBuilder $builder, array $idsParent): void
    {
        if (empty($idsParent)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                "{$this->getTable()}.`parent`",
                array_map(
                    fn (int $index, $hashe) => $builder->createNamedParameter(
                        (int) $hashe,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("parentFromList{$index}")
                    ),
                    array_keys($idsParent),
                    $idsParent
                )
            )
        );
    }

    protected function goldenCategory(): RelationInterface
    {
        return $this->hasOneThrough(
            Golden_Categories_Model::class,
            Golden_Categories_Industries_Pivot_Model::class,
            'id_category',
            'id_group',
            $this->getPrimaryKey(),
            'id_group'
        );
    }
}

/* End of file item_category_model.php */
/* Location: /tinymvc/myapp/models/item_category_model.php */
