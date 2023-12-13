<?php

declare(strict_types=1);

namespace App\Common\Database\Relations;

use App\Common\Database\Model;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Query\QueryBuilder;

final class BelongsTo extends AbstractRelation
{
    /**
     * The child model instance of the relation.
     */
    protected Model $child;

    /**
     * The foreign key of the parent model.
     */
    protected string $foreignKey;

    /**
     * The associated key on the parent model.
     */
    protected string $ownerKey;

    /**
     * The number of self joins for this relation.
     */
    protected static int $selfJoins = 0;

    /**
     * Creates instance of the relation.
     */
    public function __construct(QueryBuilder $query, Model $related, Model $child, string $foreignKey, string $ownerKey, ?string $name = null)
    {
        $this->child = $child; // same as self::$related
        $this->ownerKey = $ownerKey;
        $this->foreignKey = $foreignKey;

        parent::__construct($query, $related, $child, $name);
    }

    /**
     * Get the foreign key of the parent model.
     */
    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    /**
     * Get the fully qualified foreign key of the relationship.
     */
    public function getQualifiedForeignKey(): string
    {
        return $this->child->qualifyColumn($this->foreignKey);
    }

    /**
     * Get the associated key on the parent model.
     */
    public function getOwnerKey(): string
    {
        return $this->ownerKey;
    }

    /**
     * Get the fully qualified associated key of the relationship.
     */
    public function getQualifiedOwnerKey(): string
    {
        return $this->related->qualifyColumn($this->ownerKey);
    }

    /**
     * {@inheritdoc}
     */
    public function getExistenceCompareKey(): string
    {
        return $this->getQualifiedOwnerKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getParentKey(): string
    {
        return $this->getOwnerKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getQualifiedParentKey(): string
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

        return $builder
            ->select($columns)
            ->andWhere(
                $builder->expr()->eq(
                    $this->getQualifiedForeignKey(),
                    $this->getQualifiedOwnerKey()
                )
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function match(array &$records, Collection $results, string $relation): array
    {
        $dictionary = $this->buildDictionary($results, $this->ownerKey);
        foreach ($records as &$record) {
            if (null !== ($key = $record[$this->foreignKey] ?? null)) {
                $record[$relation] = $this->getRelationValue($dictionary, $key, 'one');
            } else {
                $record[$relation] = null;
            }
        }

        return $records;
    }

    /**
     * {@inheritdoc}
     */
    public function addEagerConstraints(array $records): void
    {
        if (empty($keys = $this->getKeys($records, $this->foreignKey))) {
            throw RelationEmptyKeysException::create($this);
        }

        $this->query->andWhere(
            $this->query->expr()->in(
                $this->getQualifiedOwnerKey(),
                array_map(
                    fn (int $index, $keys) => $this->query->createNamedParameter($keys, null, $this->nameParameter("key_{$index}")),
                    array_keys($keys),
                    $keys
                )
            )
        );
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
                    $this->getQualifiedForeignKey(),
                    $hash . '.' . $this->getOwnerKey()
                )
            )
        ;
    }
}
