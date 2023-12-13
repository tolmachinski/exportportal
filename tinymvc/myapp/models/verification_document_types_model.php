<?php

declare(strict_types=1);

use App\Common\Contracts\Document\DocumentTypeCategory;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Verification_Document_Types model.
 */
final class Verification_Document_Types_Model extends Model
{
    /**
     * The name of the "created at" column.
     */
    protected const CREATED_AT = 'document_created_at';

    /**
     * The name of the "updated at" column.
     */
    protected const UPDATED_AT = 'document_updated_at';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'accreditation_docs';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'VERIFICATION_DOCUMENT_TYPES';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_document';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id',
        self::CREATED_AT,
        'document_translated_titles',
        'document_translated_descriptions',
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'document_additional_options',
        'document_base_context',
        'document_i18n',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        // Simple casts
        'id_document'                      => Types::INTEGER,
        'document_groups'                  => Types::SIMPLE_ARRAY,
        'document_countries'               => Types::SIMPLE_ARRAY,
        'document_additional_options'      => Types::JSON,
        'document_titles'                  => Types::JSON,
        'document_industries'              => Types::SIMPLE_ARRAY,
        'document_general_countries'       => Types::BOOLEAN,
        'document_is_multiple'             => Types::BOOLEAN,
        'document_general_industries'      => Types::BOOLEAN,
        'document_groups_required'         => Types::SIMPLE_ARRAY,
        'document_i18n'                    => Types::JSON,
        'document_translated_titles'       => Types::JSON,
        'document_translated_descriptions' => Types::JSON,
        'document_base_context'            => Types::JSON,
        'document_created_at'              => Types::DATETIME_IMMUTABLE,
        'document_updated_at'              => Types::DATETIME_IMMUTABLE,
        'document_base_text_updated_at'    => Types::DATETIME_IMMUTABLE,
        'document_translation_updated_at'  => Types::DATETIME_IMMUTABLE,

        // Complex casts
        'document_category'                => DocumentTypeCategory::class,
    ];

    /**
     * Returns the paginator prepared for datagrid.
     */
    public function paginateForGrid(?array $filters = [], ?array $ordering = [], ?int $perPage = null, ?int $page = 1): array
    {
        $paginator = $this->getPaginator(['scopes' => $filters], $perPage, $page);
        $paginator['all'] = $this->countBy(['scopes' => $commonFilters ?? []]);
        $paginator['data'] = $this->findAllBy([
            'conditions' => $filters,
            'order'      => $ordering ?? [],
            'limit'      => $perPage,
            'skip'       => (($page ?? 1) - 1) * $perPage,
        ]);

        return $paginator;
    }

    /**
     * Scope a query by category.
     */
    protected function scopeCategory(QueryBuilder $builder, DocumentTypeCategory $category): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('document_category'),
                $builder->createNamedParameter((string) $category, ParameterType::STRING, $this->nameScopeParameter('category', true))
            )
        );
    }

    /**
     * Scope a query to filter by document types list.
     *
     * @param DocumentTypeCategory[] $categories
     */
    protected function scopeCategories(QueryBuilder $builder, array $categories)
    {
        if (empty($categories)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                $this->qualifyColumn('document_category'),
                array_map(
                    fn (int $index, $category) => $builder->createNamedParameter(
                        (string) $category,
                        ParameterType::STRING,
                        $this->nameScopeParameter("category{$index}")
                    ),
                    array_keys($categories),
                    $categories
                )
            )
        );
    }

    /**
     * Scope a query to filter by additional options lack/presence.
     */
    protected function scopeHasAdditionalOptions(QueryBuilder $builder, bool $hasOptions)
    {
        $builder->andWhere(
            $hasOptions
                ? $builder->expr()->isNotNull($this->qualifyColumn('document_additional_options'))
                : $builder->expr()->isNull($this->qualifyColumn('document_additional_options'))
        );
    }

    /**
     * Scope query for ID values.
     */
    protected function scopeIds(QueryBuilder $builder, array $documentsIds): void
    {
        if (empty($documentsIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                $this->qualifyColumn($this->getPrimaryKey()),
                array_map(
                    fn (int $i, $id) => $builder->createNamedParameter((int) $id, ParameterType::INTEGER, $this->nameScopeParameter("document_id_{$i}", true)),
                    array_keys($documentsIds),
                    $documentsIds
                )
            )
        );
    }

    /**
     * Scope query exluding specified ID values.
     */
    protected function scopeNotIds(QueryBuilder $builder, array $documentsIds): void
    {
        if (empty($documentsIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->notIn(
                $this->qualifyColumn($this->getPrimaryKey()),
                array_map(
                    fn (int $i, $id) => $builder->createNamedParameter((int) $id, ParameterType::INTEGER, $this->nameScopeParameter("not_document_id_{$i}", true)),
                    array_keys($documentsIds),
                    $documentsIds
                )
            )
        );
    }

    /**
     * Scope a query to filter by included documents.
     *
     * @deprecated v2.30.2 in favor of self::scopeIds()
     *
     * @uses self::scopeIds()
     *
     * @param mixed $included
     */
    protected function scopeInclude(QueryBuilder $builder, $included)
    {
        trigger_deprecation('db', 'v2.30.2', 'The usage of this method is deprecated. Use %s::scopeIds() instead.', __CLASS__);
        if (empty($included)) {
            return;
        }

        if (is_array($included)) {
            $list = array_map('intval', $included);
        } elseif (is_string($included) && false !== strpos($included, ',')) {
            $list = array_map('intval', explode(',', $included));
        } else {
            $list = [(int) $included];
        }

        $this->scopeIds($builder, $list);
    }

    /**
     * Scope a query to filter by excluded documents.
     *
     * @deprecated v2.30.2 in favor of self::scopeNotIds()
     *
     * @uses self::scopeNotIds()
     *
     * @param mixed $excluded
     */
    protected function scopeExclude(QueryBuilder $builder, $excluded)
    {
        trigger_deprecation('db', 'v2.30.2', 'The usage of this method is deprecated. Use %s::scopeNotIds() instead.', __CLASS__);
        if (empty($excluded)) {
            return;
        }

        if (is_array($excluded)) {
            $list = array_map('intval', $excluded);
        } elseif (is_string($excluded) && false !== strpos($excluded, ',')) {
            $list = array_map('intval', explode(',', $excluded));
        } else {
            $list = [(int) $excluded];
        }

        $this->scopeNotIds($builder, $list);
    }

    /**
     * Scope a query by existing localization.
     */
    protected function scopeHasLocale(QueryBuilder $builder, string $locale): void
    {
        $localeValueParameter = $builder->createNamedParameter("$.{$locale}.*.value", ParameterType::STRING, $this->nameScopeParameter('locale_value', true));
        $filterParameter = $builder->createNamedParameter("$.{$locale}", ParameterType::STRING, $this->nameScopeParameter('locale', true));
        $builder
            ->select(...[...$builder->getQueryPart('select') ?? [], "JSON_EXTRACT(document_i18n, {$localeValueParameter}) as `translated_language_values`"])
            ->andWhere(
                <<<CONDITION
                JSON_CONTAINS_PATH(document_i18n, 'one', {$filterParameter}) AND
                NOT JSON_CONTAINS(document_i18n->>{$localeValueParameter}, JSON_ARRAY(null)) AND
                NOT JSON_CONTAINS(document_i18n->>{$localeValueParameter}, JSON_ARRAY(""))
                CONDITION
            )
        ;
    }

    /**
     * Scope a query by not existing localization.
     */
    protected function scopeHasNoLocale(QueryBuilder $builder, string $locale): void
    {
        $localeValueParameter = $builder->createNamedParameter("$.{$locale}.*.value", ParameterType::STRING, $this->nameScopeParameter('locale_value', true));
        $filterParameter = $builder->createNamedParameter("$.{$locale}", ParameterType::STRING, $this->nameScopeParameter('locale', true));
        $builder
            ->select(...[...$builder->getQueryPart('select') ?? [], "JSON_EXTRACT(document_i18n, {$localeValueParameter}) as `translated_language_values`"])
            ->andWhere(
                <<<CONDITION
                NOT JSON_CONTAINS_PATH(document_i18n, 'one', {$filterParameter}) OR
                JSON_CONTAINS(document_i18n->>{$localeValueParameter}, JSON_ARRAY(null)) OR
                JSON_CONTAINS(document_i18n->>{$localeValueParameter}, JSON_ARRAY(""))
                CONDITION
            )
        ;
    }

    /**
     * Scope a query to filter by update date from.
     */
    protected function scopeOriginalUpdatedFromDate(QueryBuilder $builder, DateTimeInterface $updatedAt)
    {
        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->getTable()}.document_base_text_updated_at)",
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('document_base_text_updated_at', $updatedAt),
                    ParameterType::STRING,
                    $this->nameScopeParameter('original_updated_from_date', true)
                )
            )
        );
    }

    /**
     * Scope a query to filter by update date to.
     */
    protected function scopeOriginalUpdatedToDate(QueryBuilder $builder, DateTimeInterface $updatedAt)
    {
        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->getTable()}.document_base_text_updated_at)",
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('document_base_text_updated_at', $updatedAt),
                    ParameterType::STRING,
                    $this->nameScopeParameter('original_updated_to_date', true)
                )
            )
        );
    }

    /**
     * Scope query for groups ids. Require join with table accreditation_docs_groups_relation
     *
     * @param QueryBuilder $builder
     * @param int[] $groupsIds
     */
    protected function scopeDocumentGroupsIds(QueryBuilder $builder, array $groupsIds): void
    {
        /** @var Verification_Document_Types_Groups_Pivot_Model $docsGroupsPivotModel */
        $docsGroupsPivotModel = model(Verification_Document_Types_Groups_Pivot_Model::class);

        $builder->andWhere(
            $builder->expr()->in(
                "`{$docsGroupsPivotModel->getTable()}`.`id_group`",
                array_map(
                    fn ($i, $groupId) => $builder->createNamedParameter((int) $groupId, ParameterType::INTEGER, $this->nameScopeParameter("docsGroupId{$i}")),
                    array_keys($groupsIds),
                    $groupsIds
                )
            )
        );
    }

    /**
     * Relation with the documents.
     */
    protected function documents(): RelationInterface
    {
        return $this->hasMany(Verification_Documents_Model::class, 'id_type')->enableNativeCast();
    }

    /**
     * Relation with the group references.
     */
    protected function groupsReference(): RelationInterface
    {
        return $this->hasMany(Verification_Document_Types_Groups_Pivot_Model::class, 'id_document')->enableNativeCast();
    }

    /**
     * Relation with the countries references.
     */
    protected function countriesReference(): RelationInterface
    {
        return $this->hasMany(Verification_Document_Types_Countries_Pivot_Model::class, 'id_document')->enableNativeCast();
    }

    /**
     * Relation with the industries references.
     */
    protected function industriesReference(): RelationInterface
    {
        return $this->hasMany(Verification_Document_Types_Industries_Pivot_Model::class, 'id_document')->enableNativeCast();
    }

    /**
     * Relation with the groups.
     */
    protected function groups(): RelationInterface
    {
        $userGroups = $this->resolveRelatedModel(User_Groups_Model::class);
        $relation = $this->hasMany(Verification_Document_Types_Groups_Pivot_Model::class, 'id_document');
        $builder = $relation->getQuery();
        $related = $relation->getRelated();
        $relation->getRelated()->mergeCasts($userGroups->getCasts());
        $relation->enableNativeCast();
        $builder
            ->select(
                $relation->getExistenceCompareKey(),
                sprintf('%s.*', $userGroupsTable = $userGroups->getTable())
            )
            ->innerJoin(
                $table = $related->getTable(),
                $userGroupsTable,
                null,
                "{$userGroupsTable}.{$userGroups->getPrimaryKey()} = {$table}.id_group"
            )
        ;

        return $relation;
    }

    /**
     * Relation with the countries.
     */
    protected function countries(): RelationInterface
    {
        $countries = $this->resolveRelatedModel(Countries_Model::class);
        $relation = $this->hasMany(Verification_Document_Types_Countries_Pivot_Model::class, 'id_document');
        $builder = $relation->getQuery();
        $related = $relation->getRelated();
        $relation->getRelated()->mergeCasts($countries->getCasts());
        $relation->enableNativeCast();
        $builder
            ->select(
                $relation->getExistenceCompareKey(),
                sprintf('%s.*', $countriesTable = $countries->getTable())
            )
            ->innerJoin(
                $table = $related->getTable(),
                $countriesTable,
                null,
                "{$countriesTable}.{$countries->getPrimaryKey()} = {$table}.id_country"
            )
        ;

        return $relation;
    }

    /**
     * Relation with the industries.
     */
    protected function industries(): RelationInterface
    {
        $industries = $this->resolveRelatedModel(Categories_Model::class);
        $relation = $this->hasMany(Verification_Document_Types_Industries_Pivot_Model::class, 'id_document');
        $builder = $relation->getQuery();
        $related = $relation->getRelated();
        $relation->getRelated()->mergeCasts($industries->getCasts());
        $relation->enableNativeCast();
        $builder
            ->select(
                $relation->getExistenceCompareKey(),
                sprintf('%s.*', $industriesTable = $industries->getTable())
            )
            ->innerJoin(
                $table = $related->getTable(),
                $industriesTable,
                null,
                "{$industriesTable}.{$industries->getPrimaryKey()} = {$table}.id_industry"
            )
        ;

        return $relation;
    }

    /**
     * Scope for inner join with accreditation_docs_groups_relation table
     *
     * @param QueryBuilder $builder
     * @return void
     */
    protected function bindVerificationDocsGroupsRelationInnerJoin(QueryBuilder $builder): void
    {
        /** @var Verification_Document_Types_Groups_Pivot_Model $docsGroupsPivotModel */
        $docsGroupsPivotModel = model(Verification_Document_Types_Groups_Pivot_Model::class);

        $docsGroupsPivotTable = $docsGroupsPivotModel->getTable();
        $thisTable = $this->getTable();

        $builder
            ->innerJoin(
                $thisTable,
                $docsGroupsPivotTable,
                $docsGroupsPivotTable,
                "`{$thisTable}`.`id_document` = `{$docsGroupsPivotTable}`.`id_document`"
            );
    }
}

// End of file verification_document_types_model.php
// Location: /tinymvc/myapp/models/verification_document_types_model.php
