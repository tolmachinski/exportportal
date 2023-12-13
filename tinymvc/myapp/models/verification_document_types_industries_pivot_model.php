<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Verification_Document_Types_Industries_Pivot model.
 */
final class Verification_Document_Types_Industries_Pivot_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'verification_documents_industries_relation';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'VERIFICATION_DOCUMENT_TYPES_INDUSTRIES_PIVOT';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_relation_document_industry';

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_relation_document_industry' => Types::INTEGER,
        'id_industry'                   => Types::INTEGER,
        'id_document'                   => Types::INTEGER,
    ];

    /**
     * Scope query for industry.
     */
    protected function scopeIndustry(QueryBuilder $builder, int $industryId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_industry'),
                $builder->createNamedParameter($industryId, ParameterType::INTEGER, $this->nameScopeParameter('industry_id', true))
            )
        );
    }

    /**
     * Scope query for not industry.
     */
    protected function scopeNotIndustry(QueryBuilder $builder, int $industryId): void
    {
        $builder->andWhere(
            $builder->expr()->neq(
                $this->qualifyColumn('id_industry'),
                $builder->createNamedParameter($industryId, ParameterType::INTEGER, $this->nameScopeParameter('not_industry_id', true))
            )
        );
    }

    /**
     * Scope query for industries.
     */
    protected function scopeIndustries(QueryBuilder $builder, array $industryIds): void
    {
        if (empty($industryIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                $this->qualifyColumn('id_industry'),
                array_map(
                    fn (int $i, $id) => $builder->createNamedParameter((int) $id, ParameterType::INTEGER, $this->nameScopeParameter("industries_{$i}", true)),
                    array_keys($industryIds),
                    $industryIds
                )
            )
        );
    }

    /**
     * Scope query for industries except those ones.
     */
    protected function scopeNotIndustries(QueryBuilder $builder, array $industryIds): void
    {
        if (empty($industryIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->notIn(
                $this->qualifyColumn('id_industry'),
                array_map(
                    fn (int $i, $id) => $builder->createNamedParameter((int) $id, ParameterType::INTEGER, $this->nameScopeParameter("not_industries_{$i}", true)),
                    array_keys($industryIds),
                    $industryIds
                )
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
     * Relation with the industries.
     */
    protected function industry(): RelationInterface
    {
        return $this->belongsTo(Countries_Model::class, 'id_industry')->enableNativeCast();
    }

    /**
     * Relation with the document type.
     */
    protected function documentType(): RelationInterface
    {
        return $this->belongsTo(Verification_Document_Types_Model::class, 'id_document')->enableNativeCast();
    }
}

// End of file verification_document_types_industries_pivot_model.php
// Location: /tinymvc/myapp/models/verification_document_types_industries_pivot_model.php
