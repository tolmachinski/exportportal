<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Countries_Model model.
 */
final class Countries_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'port_country';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'COUNTRIES';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';

    /**
     * {@inheritdoc}
     */
    protected array $guarded = [
        'id',
        'old_id',
    ];

    /**
     * {@inheritdoc}
     */
    protected array $nullable = [
        'translations_data',
        'position_on_select',
    ];

    /**
     * {@inheritdoc}
     */
    protected array $casts = [
        'id'                 => Types::INTEGER,
        'zip'                => Types::INTEGER,
        'old_id'             => Types::INTEGER,
        'id_continent'       => Types::INTEGER,
        'position_on_select' => Types::INTEGER,
        'country_longitude'  => Types::DECIMAL,
        'country_latitude'   => Types::DECIMAL,
        'is_focus_country'   => Types::BOOLEAN,
        'translations_data'  => Types::JSON,
    ];

    /**
     * Update country data in MySQL.
     *
     * @param int   $countryId
     * @param array $additionalData - will be used for updating data in elasticsearch
     */
    public function updateOne($countryId, array $country, array $additionalData = []): bool
    {
        if (!parent::updateOne($countryId, $country)) {
            return false;
        }

        //region update country in Elasticsearch_Countries_Model
        $updatedCountryColumns = array_flip(array_keys($country));

        $esCountryUpdates = array_filter(
            [
                'position_on_select'    => isset($updatedCountryColumns['position_on_select']) ? (int) $country['position_on_select'] : null,
                'is_focus_country'      => isset($updatedCountryColumns['is_focus_country']) ? (int) $country['is_focus_country'] : null,
                'ascii_name'            => $country['country_ascii_name'] ?? null,
                'alias'                 => $country['country_alias'] ?? null,
                'name'                  => $country['country'] ?? null,
                'abr3'                  => $country['abr3'] ?? null,
                'abr'                   => $country['abr'] ?? null,
            ],
            fn ($filteredValue) => null !== $filteredValue
        );

        if (isset($updatedCountryColumns['country_latitude'])) {
            $esCountryUpdates['location']['lat'] = (float) $country['country_latitude'];
        }

        if (isset($updatedCountryColumns['country_longitude'])) {
            $esCountryUpdates['location']['lon'] = (float) $country['country_longitude'];
        }

        if (!empty($additionalData['continent'])) {
            $esCountryUpdates['continent']['id'] = (int) $additionalData['continent']['id_continent'];
            $esCountryUpdates['continent']['name'] = $additionalData['continent']['name_continent'];
        }

        /** @var Elasticsearch_Countries_Model $elasticsearchCountriesModel */
        $elasticsearchCountriesModel = model(Elasticsearch_Countries_Model::class);

        $elasticsearchCountriesModel->updateCountryById($countryId, $esCountryUpdates);
        //endregion update country in Elasticsearch_Countries_Model

        //region update country in Elasticsearch_States_Model
        /** @var Elasticsearch_States_Model $elasticsearchStatesModel */
        $elasticsearchStatesModel = model(Elasticsearch_States_Model::class);

        $elasticsearchStatesModel->updateCountryByCountryId($countryId);
        //endregion update country in Elasticsearch_States_Model

        //region update country in Elasticsearch_Cities_Model
        /** @var Elasticsearch_Cities_Model $elasticsearchCitiesModel */
        $elasticsearchCitiesModel = model(Elasticsearch_Cities_Model::class);

        $elasticsearchCitiesModel->updateCitiesCountryByCountryId($countryId);
        //endregion update country in Elasticsearch_Cities_Model

        return true;
    }

    /**
     * Scope a query by country id.
     */
    protected function scopeId(QueryBuilder $builder, int $countryId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`id`",
                $builder->createNamedParameter($countryId, ParameterType::INTEGER, $this->nameScopeParameter('country_id', true))
            )
        );
    }

    /**
     * Scope a query by focus country.
     */
    protected function scopeIsFocusCountry(QueryBuilder $builder, int $isFocusCountry): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`is_focus_country`",
                $builder->createNamedParameter($isFocusCountry, ParameterType::INTEGER, $this->nameScopeParameter('is_focus_country', true))
            )
        );
    }

    /**
     * Scope a query by continent id.
     */
    protected function scopeContinentId(QueryBuilder $builder, int $continentId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`id_continent`",
                $builder->createNamedParameter($continentId, ParameterType::INTEGER, $this->nameScopeParameter('continent_id', true))
            )
        );
    }

    /**
     * Scope a query by country name.
     */
    protected function scopeCountry(QueryBuilder $builder, string $countryName): void
    {
        $builder->andWhere(
            $builder->expr()->like(
                "`{$this->getTable()}`.`country`",
                $builder->createNamedParameter($countryName . '%', ParameterType::STRING, $this->nameScopeParameter('country_name', true))
            )
        );
    }

    /**
     * Scope a query by country special position.
     */
    protected function scopeHasSpecialPosition(QueryBuilder $builder, bool $hasSpecialPosition): void
    {
        if ($hasSpecialPosition) {
            $builder->andWhere(
                $builder->expr()->isNotNull("`{$this->getTable()}`.`position_on_select`")
            );
        } else {
            $builder->andWhere(
                $builder->expr()->isNull("`{$this->getTable()}`.`position_on_select`")
            );
        }
    }

    /**
     * Scope a query by country code.
     */
    protected function scopeCountryCode(QueryBuilder $builder, string $countryCode): void
    {
        /** @var Phone_Codes_Model $portCountryCodesModel */
        $portCountryCodesModel = model(Phone_Codes_Model::class);

        $builder->andWhere(
            $builder->expr()->eq(
                $portCountryCodesModel->qualifyColumn('ccode'),
                $builder->createNamedParameter($countryCode, ParameterType::STRING, $this->nameScopeParameter('country_code', true))
            )
        );
    }

    /**
     * Relation with the continent.
     */
    protected function continent(): RelationInterface
    {
        return $this->belongsTo(Continents_Model::class, 'id_continent', 'id_continent')->enableNativeCast();
    }

    /**
     * Relation with the continent.
     *
     * @deprecated v2.29.6 in favor of self::phoneCodes()
     * @see self::phoneCodes()
     */
    protected function countryCode(): RelationInterface
    {
        trigger_deprecation('app', '2.29.6', 'You must use %s::phoneCodes() to get relation to the phone codes', __CLASS__);

        return $this->phoneCodes()->enableNativeCast()->setName('countryCode');
    }

    /**
     * Relation with the continent.
     */
    protected function phoneCodes(): RelationInterface
    {
        return $this->hasMany(Phone_Codes_Model::class, 'id_country', 'id')->enableNativeCast();
    }

    /**
     * Scope for join with country codes table.
     */
    protected function bindCountryCodes(QueryBuilder $builder): void
    {
        /** @var Phone_Codes_Model $portCountryCodesModel */
        $portCountryCodesModel = model(Phone_Codes_Model::class);
        $countryCodesTable = $portCountryCodesModel->getTable();

        $builder
            ->leftJoin(
                $this->getTable(),
                $countryCodesTable,
                $countryCodesTable,
                "`{$countryCodesTable}`.`id_country` = `{$this->getTable()}`.`id`"
            )
        ;
    }
}

// End of file countries_model.php
// Location: /tinymvc/myapp/models/countries_model.php
