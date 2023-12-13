<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Phone_Codes_Model model.
 */
final class Phone_Codes_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'port_country_codes';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'PHONE_CODES';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_code';

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_country'    => Types::INTEGER,
        'id_code'       => Types::INTEGER,
    ];

    /**
     * Scope a query for ID.
     */
    protected function scopeId(QueryBuilder $builder, int $codeId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->getPrimaryKey(),
                $builder->createNamedParameter($codeId, ParameterType::INTEGER, $this->nameScopeParameter('phone_code_id', true))
            )
        );
    }

    /**
     * Scope a query for IDs.
     */
    protected function scopeIds(QueryBuilder $builder, array $codeIds): void
    {
        if (empty($codeIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in($this->getPrimaryKey(), array_map(
                fn (int $i, $id) => $builder->createNamedParameter((int) $id, ParameterType::INTEGER, $this->nameScopeParameter("phone_code_id_{$i}", true)),
                array_keys($codeIds),
                $codeIds
            ))
        );
    }

    /**
     * Scope a query for country.
     */
    protected function scopeCountry(QueryBuilder $builder, int $countryId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_country'),
                $builder->createNamedParameter($countryId, ParameterType::INTEGER, $this->nameScopeParameter('country_id', true))
            )
        );
    }

    /**
     * Scope an extended query.
     */
    protected function scopeExtendedList(QueryBuilder $builder): void
    {
        $countriesRelation = $this->country();
        $countries = $countriesRelation->getRelated();
        $builder
            ->select(
                $this->qualifyColumn('*'),
                "{$countries->qualifyColumn('country')} AS `country_name`",
                "{$countries->qualifyColumn('abr')} AS `country_iso3166_alpha2`",
                "{$countries->qualifyColumn('abr3')} AS `country_iso3166_alpha3`",
                $countries->qualifyColumn('country_latitude'),
                $countries->qualifyColumn('country_longitude'),
            )
            ->leftJoin(
                $this->getTable(),
                $countries->getTable(),
                null,
                "{$countriesRelation->getQualifiedParentKey()} = {$countriesRelation->getExistenceCompareKey()}"
            )
        ;
    }

    /**
     * Scope a query by phone code.
     */
    protected function scopePhoneCode(QueryBuilder $builder, string $countryCode): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('ccode'),
                $builder->createNamedParameter($countryCode, ParameterType::STRING, $this->nameScopeParameter('phone_code', true))
            )
        );
    }

    /**
     * Scope a query by country code.
     *
     * @deprecated v2.29.6 in favor of self::scopePhoneCode()
     * @see self::scopePhoneCode()
     */
    protected function scopeCcode(QueryBuilder $builder, string $countryCode): void
    {
        $this->scopePhoneCode($builder, $countryCode);
    }

    /**
     * Relation with country.
     */
    protected function country(): RelationInterface
    {
        return $this->belongsTo(Countries_Model::class, 'id_country')->enableNativeCast();
    }
}

// End of file phone_codes_model.php
// Location: /tinymvc/myapp/models/phone_codes_model.php
