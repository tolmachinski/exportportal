<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Verification_Document_Types_Countries_Pivot model.
 */
final class Verification_Document_Types_Countries_Pivot_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'verification_documents_countries_relation';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'VERIFICATION_DOCUMENT_TYPES_COUNTRIES_PIVOT';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = ['id_country', 'id_document'];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_country'  => Types::INTEGER,
        'id_document' => Types::INTEGER,
    ];

    /**
     * Scope query for user group.
     */
    protected function scopeCountry(QueryBuilder $builder, int $countryId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_country',
                $builder->createNamedParameter($countryId, ParameterType::INTEGER, $this->nameScopeParameter('country_id', true))
            )
        );
    }

    /**
     * Scope query for user group.
     */
    protected function scopeNotCountry(QueryBuilder $builder, int $countryId): void
    {
        $builder->andWhere(
            $builder->expr()->neq(
                $this->qualifyColumn('id_country'),
                $builder->createNamedParameter($countryId, ParameterType::INTEGER, $this->nameScopeParameter('not_country_id', true))
            )
        );
    }

    /**
     * Scope query for document type.
     */
    protected function scopeDocumentType(QueryBuilder $builder, int $typeId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_document'),
                $builder->createNamedParameter($typeId, ParameterType::INTEGER, $this->nameScopeParameter('document_type_id', true))
            )
        );
    }

    /**
     * Scope query for not document type.
     */
    protected function scopeNotDocumentType(QueryBuilder $builder, int $typeId): void
    {
        $builder->andWhere(
            $builder->expr()->neq(
                $this->qualifyColumn('id_document'),
                $builder->createNamedParameter($typeId, ParameterType::INTEGER, $this->nameScopeParameter('not_document_type_id', true))
            )
        );
    }

    /**
     * Relation with the country.
     */
    protected function country(): RelationInterface
    {
        return $this->belongsTo(Countries_Model::class, 'id_country')->enableNativeCast();
    }

    /**
     * Relation with the document type.
     */
    protected function documentType(): RelationInterface
    {
        return $this->belongsTo(Verification_Document_Types_Model::class, 'id_document')->enableNativeCast();
    }
}

// End of file verification_document_types_countries_pivot_model.php
// Location: /tinymvc/myapp/models/verification_document_types_countries_pivot_model.php
