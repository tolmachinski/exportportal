<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * States model.
 */
final class States_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'states';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'STATES';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_country'            => Types::INTEGER,
        'id'                    => Types::INTEGER,
    ];

    /**
     * Scope a query by state id.
     */
    protected function scopeId(QueryBuilder $builder, int $stateId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`id`",
                $builder->createNamedParameter($stateId, ParameterType::INTEGER, $this->nameScopeParameter('state_id'))
            )
        );
    }

    /**
     * Scope a query by country ID.
     */
    protected function scopeCountry(QueryBuilder $builder, int $countryId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_country'),
                $builder->createNamedParameter($countryId, ParameterType::INTEGER, $this->nameScopeParameter('country_id'))
            )
        );
    }

    /**
     * Scope a query by country id.
     *
     * @param QueryBuilder $builder
     * @param int $countryId
     *
     * @return void
     */
    protected function scopeCountryId(QueryBuilder $builder, int $countryId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`id_country`",
                $builder->createNamedParameter($countryId, ParameterType::INTEGER, $this->nameScopeParameter('countryId'))
            )
        );
    }

    /**
     * Relation with the continent.
     */
    protected function country(): RelationInterface
    {
        return $this->belongsTo(Countries_Model::class, 'id_country', 'id')->enableNativeCast();
    }
}

// End of file states_model.php
// Location: /tinymvc/myapp/models/states_model.php
