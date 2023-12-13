<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Types\Types as CustomTypes;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Categories model.
 */
final class Categories_Model extends Model
{
    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = false;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'item_category';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'CATEGORIES';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'category_id';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'category_id',
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'keywords',
        'description',
        'translations_data',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'category_id'       => Types::INTEGER,
        'industry_id'       => Types::INTEGER,
        'parent'            => Types::INTEGER,
        'p_or_m'            => Types::INTEGER,
        'vin'               => Types::INTEGER,
        'cat_type'          => Types::INTEGER,
        'cat_childrens'     => Types::SIMPLE_ARRAY,
        'breadcrumbs'       => CustomTypes::SIMPLE_JSON_ARRAY,
        'actualized'        => Types::BOOLEAN,
        'feature_price'     => CustomTypes::SIMPLE_MONEY,
        'highlight_price'   => CustomTypes::SIMPLE_MONEY,
        'translations_data' => Types::JSON,
        'is_restricted'     => Types::BOOLEAN,
    ];

    /**
     * Scope query for groups.
     */
    protected function scopeIds(QueryBuilder $builder, array $categoriesId): void
    {
        if (empty($categoriesId)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                $this->qualifyColumn($this->getPrimaryKey()),
                array_map(
                    fn (int $i, $id) => $builder->createNamedParameter((int) $id, ParameterType::INTEGER, $this->nameScopeParameter("category_id_{$i}", true)),
                    array_keys($categoriesId),
                    $categoriesId
                )
            )
        );
    }

    /**
     * Scope query for industry/not industry status.
     */
    protected function scopeIsIndustry(QueryBuilder $builder, bool $isIndustry): void
    {
        $parameter = $builder->createNamedParameter(0, ParameterType::INTEGER, $this->nameScopeParameter('is_industry', true));
        if ($isIndustry) {
            $builder->andWhere(
                $builder->expr()->eq('parent', $parameter)
            );
        } else {
            $builder->andWhere(
                $builder->expr()->neq('parent', $parameter)
            );
        }
    }

    /**
     * Scope query for parent.
     */
    protected function scopeHasChildren(QueryBuilder $builder, bool $hasChildren): void
    {
        $parameter = $builder->createNamedParameter('', ParameterType::STRING, $this->nameScopeParameter('has_children', true));
        if ($hasChildren) {
            $builder->andWhere(
                $builder->expr()->neq('cat_childrens', $parameter)
            );
        } else {
            $builder->andWhere(
                $builder->expr()->eq('cat_childrens', $parameter)
            );
        }
    }

    /**
     * Scope query for parent.
     */
    protected function scopeParent(QueryBuilder $builder, int $parentId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'parent',
                $builder->createNamedParameter($parentId, ParameterType::INTEGER, $this->nameScopeParameter('parent_id', true))
            )
        );
    }

    /**
     * Scope query for parents.
     *
     * @param int[] $parentIds
     */
    protected function scopeParents(QueryBuilder $builder, array $parentIds): void
    {
        if (empty($parentIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                $this->qualifyColumn('parent'),
                array_map(
                    fn (int $i, $id) => $builder->createNamedParameter((int) $id, ParameterType::INTEGER, $this->nameScopeParameter("parent_id_{$i}", true)),
                    array_keys($parentIds),
                    $parentIds
                )
            )
        );
    }

}

// End of file categories_model.php
// Location: /tinymvc/myapp/models/categories_model.php
