<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Upgrade_Benefits model.
 */
final class Upgrade_Benefits_Model extends Model
{
    /**
     * The name of the "created at" column.
     */
    protected const CREATED_AT = 'benefit_created_on';

    /**
     * The name of the "updated at" column.
     */
    protected const UPDATED_AT = 'benefit_updated_on';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'upgrade_benefits';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'UPGRADE_BENEFITS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_benefit';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id_benefit',
        self::CREATED_AT,
        self::UPDATED_AT,
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'benefit_groups',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_benefit'         => Types::INTEGER,
        'benefit_groups'     => Types::SIMPLE_ARRAY,
        'benefit_weight'     => Types::INTEGER,
        'benefit_created_on' => Types::DATETIME_IMMUTABLE,
        'benefit_updated_on' => Types::DATETIME_IMMUTABLE,
    ];

    /**
     * Scope a query to filter by request by groups.
     */
    protected function scopeGroups(QueryBuilder $builder, array $groups)
    {
        if (empty($groups)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->and(
                ...array_map(
                    fn (int $i, $group) => sprintf('FIND_IN_SET(%s, `benefit_groups`)', $builder->createNamedParameter(
                        (int) $group,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("group_id_{$i}")
                    )),
                    array_keys($groups),
                    $groups,
                )
            )
        );
    }
}

// End of file upgrade_benefits_model.php
// Location: /tinymvc/myapp/models/upgrade_benefits_model.php
