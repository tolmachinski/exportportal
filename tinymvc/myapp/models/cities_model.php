<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Cities model.
 */
final class Cities_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'zips';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'CITIES';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_country'    => Types::INTEGER,
        'state'         => Types::INTEGER,
        'id'            => Types::INTEGER,
    ];

    /**
     * Scope a query by city id.
     */
    protected function scopeId(QueryBuilder $builder, int $cityId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`id`",
                $builder->createNamedParameter($cityId, ParameterType::INTEGER, $this->nameScopeParameter('cityId'))
            )
        );
    }

    /**
     * Relation with the country.
     */
    protected function country(): RelationInterface
    {
        /** @var RelationInterface $relation */
        $relation = $this->belongsTo(Countries_Model::class, 'id_country', 'id')->enableNativeCast();

        $countryTable = $relation->getRelated()->getTable();

        $builder = $relation->getQuery();
        $builder
            ->select(
                "`{$countryTable}`.`id`",
                "`{$countryTable}`.`country`",
                "`{$countryTable}`.`country_ascii_name`",
                "`{$countryTable}`.`country_alias`",
                "`{$countryTable}`.`abr`",
                "`{$countryTable}`.`abr3`",
            )
        ;

        return $relation;
    }

    /**
     * Scope for join with countries.
     */
    protected function bindCountries(QueryBuilder $builder): void
    {
        /** @var Countries_Model $portCountryModel */
        $portCountryModel = model(Countries_Model::class);
        $countryTable = $portCountryModel->getTable();

        $builder
            ->leftJoin(
                $this->getTable(),
                $countryTable,
                $countryTable,
                "`{$countryTable}`.`{$portCountryModel->getPrimaryKey()}` = `{$this->getTable()}`.`id_country`"
            )
        ;
    }

    /**
     * Scope for join with states.
     */
    protected function bindStates(QueryBuilder $builder): void
    {
        /** @var States_Model $statesModel */
        $statesModel = model(States_Model::class);
        $stateTable = $statesModel->getTable();

        $builder
            ->leftJoin(
                $this->getTable(),
                $stateTable,
                $stateTable,
                "`{$stateTable}`.`{$statesModel->getPrimaryKey()}` = `{$this->getTable()}`.`state`"
            )
        ;
    }
}

// End of file cities_model.php
// Location: /tinymvc/myapp/models/cities_model.php
