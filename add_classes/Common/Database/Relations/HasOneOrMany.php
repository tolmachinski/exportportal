<?php

declare(strict_types=1);

namespace App\Common\Database\Relations;

use App\Common\Database\Model;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Query\QueryBuilder;

abstract class HasOneOrMany extends AbstractRelation
{
    /**
     * The foreign key of the parent model.
     */
    protected string $foreignKey;

    /**
     * The local key of the parent model.
     */
    protected string $localKey;

    /**
     * Creates instance of the relation.
     */
    public function __construct(QueryBuilder $query, Model $related, Model $parent, string $foreignKey, string $localKey, ?string $name = null)
    {
        $this->localKey = $localKey;
        $this->foreignKey = $foreignKey;

        parent::__construct($query, $related, $parent, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function addEagerConstraints(array $models): void
    {
        if (empty($keys = $this->getKeys($models, $this->localKey))) {
            throw RelationEmptyKeysException::create($this);
        }

        $this->query->andWhere(
            $this->query->expr()->in(
                $this->getQualifiedForeignKey(),
                array_map(
                    fn (int $index, $keys) => $this->query->createNamedParameter($keys, null, $this->nameParameter("key_{$index}")),
                    array_keys($keys),
                    $keys
                )
            )
        );
    }

    /**
     * Get the plain foreign key.
     */
    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    /**
     * Get the foreign key for the relationship.
     */
    public function getQualifiedForeignKey(): string
    {
        return $this->related->qualifyColumn($this->foreignKey);
    }

    /**
     * {@inheritdoc}
     */
    public function getParentKey(): string
    {
        return $this->localKey;
    }

    /**
     * {@inheritdoc}
     */
    public function getQualifiedParentKey(): string
    {
        return $this->parent->qualifyColumn($this->localKey);
    }

    /**
     * {@inheritdoc}
     */
    public function getExistenceCompareKey(): string
    {
        return $this->getQualifiedForeignKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getExistenceQuery(QueryBuilder $builder, QueryBuilder $parentBuilder, array $columns = ['*']): QueryBuilder
    {
        $childTable = $builder->getQueryPart('from')[0]['table'] ?? null;
        $parentTable = $parentBuilder->getQueryPart('from')[0]['table'] ?? null;
        if ($parentTable && $childTable && $parentTable === $childTable) {
            return $this->getSelfRelatedExistenceQuery($builder, $parentBuilder, $columns);
        }

        return parent::getExistenceQuery($builder, $parentBuilder, $columns);
    }

    /**
     * Match the eagerly loaded results to their many parents.
     */
    protected function matchRecords(array &$records, Collection $results, string $relation, string $type): array
    {
        $dictionary = $this->buildDictionary($results, $this->foreignKey);
        foreach ($records as &$record) {
            if (null !== ($key = $record[$this->localKey] ?? null)) {
                $record[$relation] = $this->getRelationValue($dictionary, $key, $type);
            } else {
                $record[$relation] = null;
            }
        }

        return $records;
    }

    /**
     * Add the constraints for a relationship query on the same table.
     */
    private function getSelfRelatedExistenceQuery(QueryBuilder $builder, QueryBuilder $parentBuilder, array $columns = ['*']): QueryBuilder
    {
        return $builder
            ->select($columns)
            ->resetQueryPart('from')
            ->from($this->related->getTable(), $hash = $this->getRelationCountHash())
            ->andWhere(
                $builder->expr()->eq(
                    $hash . '.' . $this->getForeignKey(),
                    $this->getQualifiedParentKey()
                )
            )
        ;
    }
}
