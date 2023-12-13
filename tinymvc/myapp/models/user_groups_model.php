<?php

declare(strict_types=1);

use App\Casts\Group\GroupAliasCast;
use App\Casts\Group\GroupTypeCast;
use App\Common\Contracts\Group\GroupAlias;
use App\Common\Contracts\Group\GroupType;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * User_Groups model.
 */
final class User_Groups_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'user_groups';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'USER_GROUPS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'idgroup';

    /**
     * {@inheritdoc}
     */
    protected array $nullable = [
        'menu',
    ];

    /**
     * {@inheritdoc}
     */
    protected array $guarded = [
        'idgroup',
    ];

    /**
     * {@inheritdoc}
     */
    protected array $casts = [
        // Simple casts
        'idgroup'                     => Types::INTEGER,
        'gr_priority'                 => Types::INTEGER,
        'gr_lang_restriction_enabled' => Types::BOOLEAN,
        'can_delete'                  => Types::BOOLEAN,
        'statistic_columns'           => Types::SIMPLE_ARRAY,
        'notify_columns'              => Types::SIMPLE_ARRAY,
        'menu'                        => Types::JSON,

        // Complex casts
        'gr_type'                     => GroupTypeCast::class,
        'gr_alias'                    => GroupAliasCast::class,
    ];

    /**
     * Scope query for type.
     */
    protected function scopeType(QueryBuilder $builder, GroupType $type): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'gr_type',
                $builder->createNamedParameter($type->value, ParameterType::STRING, $this->nameScopeParameter('type'))
            )
        );
    }

    /**
     * Scope query for alias.
     */
    protected function scopeAlias(QueryBuilder $builder, GroupAlias $alias): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'gr_alias',
                $builder->createNamedParameter($alias->value, ParameterType::STRING, $this->nameScopeParameter('alias'))
            )
        );
    }

    /**
     * Scope query for alias.
     */
    protected function scopePriority(QueryBuilder $builder, int $priority): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'gr_priority',
                $builder->createNamedParameter($priority, ParameterType::INTEGER, $this->nameScopeParameter('priority'))
            )
        );
    }

    /**
     * Scope comment by group IDs.
     */
    protected function scopeIds(QueryBuilder $builder, array $groupIds): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                'idgroup',
                array_map(
                    fn (int $index, $groupId) => $builder->createNamedParameter((int) $groupId, ParameterType::INTEGER, $this->nameScopeParameter("id{$index}")),
                    array_keys($groupIds),
                    $groupIds
                )
            )
        );
    }

    /**
     * Scope query for aliases.
     */
    protected function scopeAliases(QueryBuilder $builder, array $aliases): void
    {
        $aliases = array_map(fn ($alias) => $alias instanceof GroupAlias ? $alias : GroupAlias::from($alias), $aliases);
        $builder->andWhere(
            $builder->expr()->in(
                'gr_alias',
                array_map(
                    fn (int $index, GroupAlias $alias) => $builder->createNamedParameter($alias->value, ParameterType::STRING, $this->nameScopeParameter("alias{$index}", true)),
                    array_keys($aliases),
                    $aliases
                )
            )
        );
    }

    /**
     * Scope query for types.
     */
    protected function scopeTypes(QueryBuilder $builder, array $groups): void
    {
        $groups = array_map(fn ($group) => $group instanceof GroupType ? $group : GroupType::from($group), $groups);
        $builder->andWhere(
            $builder->expr()->in(
                'gr_type',
                array_map(
                    fn (int $index, GroupType $group) => $builder->createNamedParameter($group->value, ParameterType::STRING, $this->nameScopeParameter("type_{$index}", true)),
                    array_keys($groups),
                    $groups
                )
            )
        );
    }

    /**
     * Scope query for types.
     *
     * @deprecated v2.28.4 in favor of self::scopeTypes()
     * @see self::scopeTypes()
     */
    protected function scopeGroups(QueryBuilder $builder, array $groups): void
    {
        $this->scopeTypes($builder, $groups);
    }

    /**
     * Get relation with rights.
     */
    protected function rights(): RelationInterface
    {
        return $this->hasManyThrough(
            Rights_Model::class,
            User_Group_Rights_Pivot_Model::class,
            'idgroup',
            'idright',
            $this->getPrimaryKey(),
            'idright'
        );
    }
}

// End of file user_groups_model.php
// Location: /tinymvc/myapp/models/user_groups_model.php
