<?php

declare(strict_types=1);

namespace App\Common\Database\Relations;

use App\Common\Database\Model;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Query\QueryBuilder;

class HasManyThrough extends AbstractRelation
{
    /**
     * The "through" parent model instance.
     */
    protected Model $throughParent;

    /**
     * The far parent model instance.
     */
    protected Model $farParent;

    /**
     * The near key on the relationship.
     *
     * @var string
     */
    protected $firstKey;

    /**
     * The far key on the relationship.
     *
     * @var string
     */
    protected $secondKey;

    /**
     * The local key on the relationship.
     *
     * @var string
     */
    protected $localKey;

    /**
     * The local key on the intermediary model.
     *
     * @var string
     */
    protected $secondLocalKey;

    /**
     * Create a new has many through relationship instance.
     */
    public function __construct(
        QueryBuilder $queryBuilder,
        Model $related,
        Model $farParent,
        Model $throughParent,
        string $firstKey,
        string $secondKey,
        string $localKey,
        string $secondLocalKey,
        ?string $name = null
    ) {
        $this->localKey = $localKey;
        $this->firstKey = $firstKey;
        $this->secondKey = $secondKey;
        $this->farParent = $farParent;
        $this->throughParent = $throughParent;
        $this->secondLocalKey = $secondLocalKey;

        parent::__construct($queryBuilder, $related, $throughParent, $name);
    }

    /**
     * {@inheritDoc}
     */
    public function getQualifiedParentKey(): string
    {
        return $this->parent->qualifyColumn($this->secondLocalKey);
    }

    /**
     * Get the qualified foreign key on the related model.
     */
    public function getQualifiedFarKey(): string
    {
        return $this->getQualifiedForeignKey();
    }

    /**
     * Get the foreign key on the "through" model.
     */
    public function getFirstKey(): string
    {
        return $this->firstKey;
    }

    /**
     * Get the qualified foreign key on the "through" model.
     */
    public function getQualifiedFirstKey(): string
    {
        return $this->throughParent->qualifyColumn($this->firstKey);
    }

    /**
     * Get the foreign key on the related model.
     */
    public function getForeignKey(): string
    {
        return $this->secondKey;
    }

    /**
     * Get the qualified foreign key on the related model.
     */
    public function getQualifiedForeignKey(): string
    {
        return $this->related->qualifyColumn($this->secondKey);
    }

    /**
     * Get the local key on the far parent model.
     */
    public function getLocalKey(): string
    {
        return $this->localKey;
    }

    /**
     * Get the qualified local key on the far parent model.
     */
    public function getQualifiedLocalKey(): string
    {
        return $this->farParent->qualifyColumn($this->localKey);
    }

    /**
     * Get the local key on the intermediary model.
     */
    public function getSecondLocalKey(): string
    {
        return $this->secondLocalKey;
    }

    /**
     * {@inheritdoc}
     */
    public function addEagerConstraints(array $records): void
    {
        if (empty($keys = $this->getKeys($records, $this->localKey))) {
            throw RelationEmptyKeysException::create($this);
        }

        $this->query->andWhere(
            $this->query->expr()->in(
                $this->getQualifiedFirstKey(),
                array_map(
                    fn (int $index, $keys) => $this->query->createNamedParameter($keys, null, $this->nameParameter("key_{$index}")),
                    array_keys($keys),
                    $keys
                )
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function match(array &$records, Collection $results, string $relation): array
    {
        // First, we will take the dictionary for current records and results list
        $dictionary = $this->buildDictionary($results, 'relation__internal_through_key');
        // After that we will spin through the parent results to
        // link them up with their children using the keyed dictionary to make the
        // matching very convenient and easy work.
        foreach ($records as &$record) {
            if (null !== ($key = $record[$this->localKey] ?? null)) {
                $record[$relation] = $this->getRelationValue($dictionary, $key, 'many');
            } else {
                $record[$relation] = null;
            }
        }

        return $records;
    }

    /**
     * {@inheritdoc}
     */
    public function get(array $columns = ['*']): Collection
    {
        $query = $this->prepareQueryBuilder($columns);
        $this->performJoin($query);
        $records = $this->getConnection()->fetchAllAssociative(
            $query->getSQL(),
            $query->getParameters(),
            (array) $query->getParameterTypes()
        );

        // We can cast attributes only in several cases.
        // First of all, the native cast must be enabled.
        // Second, the cast in related model must be enabled as well.
        if (!$this->isNativeCastEnabled() || !$this->getRelated()->isCastEnabled()) {
            return new ArrayCollection($records);
        }

        return new ArrayCollection(\array_map(
            fn ($record) => $this->getRelated()->castAttributesToNative($record),
            $records
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getExistenceQuery(QueryBuilder $query, QueryBuilder $parentQuery, array $columns = ['*']): QueryBuilder
    {
        $childTable = $query->getQueryPart('from')[0]['table'] ?? null;
        $parentTable = $parentQuery->getQueryPart('from')[0]['table'] ?? null;
        if ($childTable && $parentTable && $parentTable === $childTable) {
            return $this->getSelfRelatedExistenceQuery($query, $parentQuery, $columns);
        }

        if ($parentTable === $this->throughParent->getTable()) {
            return $this->getThroughSelfRelatedExistenceQuery($query, $parentQuery, $columns);
        }

        $this->performJoin($query);

        return $query
            ->select($columns)
            ->andWhere(
                $query->expr()->eq(
                    $this->getQualifiedLocalKey(),
                    $this->getQualifiedFirstKey()
                )
            )
        ;
    }

    /**
     * Add the constraints for a relationship query on the same table.
     */
    protected function getSelfRelatedExistenceQuery(QueryBuilder $query, QueryBuilder $parentQuery, array $columns = ['*']): QueryBuilder
    {
        $table = $query->getQueryPart('from')[0]['table'];
        $query
            ->resetQueryPart('from')
            ->from($table, $hash = $this->getRelationCountHash())
            ->innerJoin(
                $hash,
                $this->throughParent->getTable(),
                null,
                "{$this->getQualifiedParentKey()} = {$hash}.{$this->secondKey}"
            )
        ;
        // @todo Add support for sof deletes

        return $query
            ->select($columns)
            ->andWhere(
                $query->expr()->eq(
                    "{$parentQuery->getQueryPart('from')[0]['table']}.{$this->localKey}",
                    $this->getQualifiedFirstKey()
                )
            )
        ;
    }

    /**
     * Add the constraints for a relationship query on the same table as the through parent.
     */
    protected function getThroughSelfRelatedExistenceQuery(QueryBuilder $query, QueryBuilder $parentQuery, array $columns = ['*']): QueryBuilder
    {
        $table = $this->throughParent->getTable();
        $hash = $this->getRelationCountHash();
        $query
            ->innerJoin(
                $query->getQueryPart('from')[0]['table'],
                $table,
                $hash,
                "{$hash}.{$this->secondLocalKey} = {$this->getQualifiedFarKey()}"
            )
        ;
        // @todo Add support for sof deletes

        return $query
            ->select($columns)
            ->andWhere(
                $query->expr()->eq(
                    "{$parentQuery->getQueryPart('from')[0]['table']}.{$this->localKey}",
                    "{$hash}.{$this->firstKey}"
                )
            )
        ;
    }

    /**
     * Build model dictionary keyed by the relation's foreign key.
     */
    protected function buildDictionary(Collection $results, string $relationKey): array
    {
        $dictionary = [];

        // First, we will create a dictionary of results keyed by the foreign key of the
        // relationship as this will allow us to quickly access all of the related
        // records without having to do slow nested looping.
        foreach ($results->getValues() as $result) {
            $key = $result[$relationKey] ?? null;
            if (!isset($dictionary[$key])) {
                $dictionary[$key] = [];
            }
            $dictionary[$key][] = $result;
        }

        return $dictionary;
    }

    /**
     * Set the join clause on the query.
     */
    protected function performJoin(QueryBuilder $query = null): void
    {
        $query = $query ?: $this->query;
        $farKey = $this->getQualifiedFarKey();
        $query->join(
            $this->related->getTable(),
            $this->throughParent->getTable(),
            null,
            "{$this->getQualifiedParentKey()} = {$farKey}"
        );

        // @todo Add support for soft deletes
    }

    /**
     * Prepare the query builder for query execution.
     */
    protected function prepareQueryBuilder(array $columns = ['*']): QueryBuilder
    {
        $columns = empty($columns) ? ['*'] : $columns;
        $originalColumns = $this->query->getQueryPart('select') ?? [];

        return $this->query->select(
            \array_merge(
                $originalColumns,
                $this->prepareColumns(!empty($originalColumns) ? [] : $columns)
            )
        );
    }

    /**
     * Prepares the select columns for the relation query.
     */
    protected function prepareColumns(array $columns = ['*']): array
    {
        if ($columns == ['*']) {
            $columns = [$this->related->getTable() . '.*'];
        }

        return array_merge($columns, [$this->getQualifiedFirstKey() . ' as `relation__internal_through_key`']);
    }
}
