<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Verification_Document_Types_Groups_Pivot model.
 */
final class Verification_Document_Types_Groups_Pivot_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'accreditation_docs_groups_relation';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'VERIFICATION_DOCUMENT_TYPES_GROUPS_PIVOT';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'accreditation_docs_groups_rel_id';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'accreditation_docs_groups_rel_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'accreditation_docs_groups_rel_id' => Types::INTEGER,
        'id_group'                         => Types::INTEGER,
        'id_document'                      => Types::INTEGER,
        'document_required'                => Types::BOOLEAN,
        'is_required'                      => Types::BOOLEAN,
    ];

    /**
     * Scope query for user group.
     */
    protected function scopeUserGroup(QueryBuilder $builder, int $groupId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_group'),
                $builder->createNamedParameter($groupId, ParameterType::INTEGER, $this->nameScopeParameter('group_id', true))
            )
        );
    }

    /**
     * Scope query for user group.
     */
    protected function scopeNotUserGroup(QueryBuilder $builder, int $groupId): void
    {
        $builder->andWhere(
            $builder->expr()->neq(
                $this->qualifyColumn('id_group'),
                $builder->createNamedParameter($groupId, ParameterType::INTEGER, $this->nameScopeParameter('not_group_id', true))
            )
        );
    }

    /**
     * Scope query for groups.
     */
    protected function scopeGroups(QueryBuilder $builder, array $groupIds): void
    {
        if (empty($groupIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                $this->qualifyColumn('id_group'),
                array_map(
                    fn (int $i, $id) => $builder->createNamedParameter((int) $id, ParameterType::INTEGER, $this->nameScopeParameter("groups_{$i}", true)),
                    array_keys($groupIds),
                    $groupIds
                )
            )
        );
    }

    /**
     * Scope query for groups except those ones.
     */
    protected function scopeNotGroups(QueryBuilder $builder, array $groupIds): void
    {
        if (empty($groupIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->notIn(
                $this->qualifyColumn('id_group'),
                array_map(
                    fn (int $i, $id) => $builder->createNamedParameter((int) $id, ParameterType::INTEGER, $this->nameScopeParameter("not_groups_{$i}", true)),
                    array_keys($groupIds),
                    $groupIds
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
                $builder->createNamedParameter($typeId, ParameterType::INTEGER, $this->nameScopeParameter('document_id', true))
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
                $builder->createNamedParameter($typeId, ParameterType::INTEGER, $this->nameScopeParameter('not_document_id', true))
            )
        );
    }

    /**
     * Scope query for user group.
     */
    protected function scopeIsRequired(QueryBuilder $builder, bool $isRequired): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('is_required'),
                $builder->createNamedParameter($isRequired, ParameterType::BOOLEAN, $this->nameScopeParameter('is_required', true))
            )
        );
    }

    /**
     * Relation with the user group.
     */
    protected function userGroup(): RelationInterface
    {
        return $this->belongsTo(User_Groups_Model::class, 'id_group')->enableNativeCast();
    }

    /**
     * Relation with the document type.
     */
    protected function documentType(): RelationInterface
    {
        return $this->belongsTo(Verification_Document_Types_Model::class, 'id_document')->enableNativeCast();
    }
}

// End of file verification_document_types_groups_pivot_model.php
// Location: /tinymvc/myapp/models/verification_document_types_groups_pivot_model.php
