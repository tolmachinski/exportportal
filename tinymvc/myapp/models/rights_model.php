<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Rights model.
 */
final class Rights_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'rights';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'RIGHTS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'idright';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'idright',
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'r_module',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'idright'             => Types::INTEGER,
        'r_module'            => Types::INTEGER,
        'rcan_delete'         => Types::BOOLEAN,
        'has_field'           => Types::BOOLEAN,
        'for_posting_content' => Types::BOOLEAN,
        'for_pending_user'    => Types::BOOLEAN,
        'share_to_staff'      => Types::BOOLEAN,
    ];

    /**
     * Scope query for right alias.
     */
    protected function scopeAlias(QueryBuilder $builder, string $alias): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'r_alias',
                $builder->createNamedParameter($alias, ParameterType::STRING, $this->nameScopeParameter('alias'))
            )
        );
    }

    /**
     * Scope query for right aliases.
     *
     * @param string[] $aliases
     */
    protected function scopeAliases(QueryBuilder $builder, array $aliases): void
    {
        if (empty($aliases)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in('r_alias', array_map(
                fn (int $i, $alias) => $builder->createNamedParameter($alias, ParameterType::STRING, $this->nameScopeParameter("alias_{$i}")),
                array_keys(array_values($aliases)),
                $aliases
            ))
        );
    }
}

// End of file rights_model.php
// Location: /tinymvc/myapp/models/rights_model.php
