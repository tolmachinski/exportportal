<?php

declare(strict_types=1);

use App\Common\Database\Concerns;
use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Shipping_Types_Model model.
 */
final class Shipping_Types_Model extends Model
{
    use Concerns\CanSearch;
    /**
     * {@inheritdoc}
     */
    protected string $table = 'shipping_type';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'SHIPPING_TYPE';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_type';

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_type'    => Types::INTEGER,
        'created_at' => Types::DATETIME_IMMUTABLE,
        'updated_at' => Types::DATETIME_IMMUTABLE,
    ];

    /**
     * Scope query for ID.
     */
    protected function scopeId(QueryBuilder $builder, int $typeId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`id_type`",
                $builder->createNamedParameter($typeId, ParameterType::INTEGER, $this->nameScopeParameter('typeId'))
            )
        );
    }

    /**
     * Scope query for alias.
     */
    protected function scopeAlias(QueryBuilder $builder, string $typeAlias): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`type_alias`",
                $builder->createNamedParameter($typeAlias, ParameterType::STRING, $this->nameScopeParameter('typeAlias'))
            )
        );
    }

    /**
     * Scope order by keywords.
     */
    protected function scopeKeywords(QueryBuilder $builder, string $keywords): void
    {
        if (empty($keywords)) {
            return;
        }

        $this->appendSearchConditionsToQuery(
            $builder,
            $keywords,
            [],
            ['type_name'],
        );
    }

    /**
     * Scope order by order date from.
     */
    protected function scopeCreateDateGte(QueryBuilder $builder, string $createDate): void
    {
        $builder->andWhere(
            $builder->expr()->gte(
                "DATE(`{$this->getTable()}`.`created_at`)",
                $builder->createNamedParameter($createDate, ParameterType::STRING, $this->nameScopeParameter('createDateGte'))
            )
        );
    }

    /**
     * Scope order by order date to.
     */
    protected function scopeCreateDateLte(QueryBuilder $builder, string $createDate): void
    {
        $builder->andWhere(
            $builder->expr()->lte(
                "DATE(`{$this->getTable()}`.`created_at`)",
                $builder->createNamedParameter($createDate, ParameterType::STRING, $this->nameScopeParameter('createDateLte'))
            )
        );
    }

    /**
     * Scope query for isVisible.
     */
    protected function scopeIsVisible(QueryBuilder $builder, int $isVisible): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`is_visible`",
                $builder->createNamedParameter($isVisible, ParameterType::INTEGER, $this->nameScopeParameter('isVisible'))
            )
        );
    }

     /**
     * Scope a query to filter types by list of IDs.
     *
     * @param mixed $types
     */
    protected function scopeListIds(QueryBuilder $builder, array $types)
    {
        if (empty($types)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->getTable()}`.`id_type`",
                array_map(
                    fn (int $index, $type) => $builder->createNamedParameter(
                        (int) $type,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("typesIds{$index}")
                    ),
                    array_keys($types),
                    $types
                )
            )
        );
    }
}

// End of file shipping_types_model_model.php
// Location: /tinymvc/myapp/models/shipping_types_model_model.php
