<?php

declare(strict_types=1);

namespace App\Common\Database\Relations;

use App\Common\Database\Model;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

abstract class AbstractRelation implements RelationInterface
{
    /**
     * The relation name.
     */
    protected ?string $name;

    /**
     * The parent model instance.
     */
    protected Model $parent;

    /**
     * The related model instance.
     */
    protected Model $related;

    /**
     * The query builder instance.
     */
    protected QueryBuilder $query;

    /**
     * The databse connection instance.
     */
    protected Connection $connection;

    /**
     * The flag that indicates if native cast is enabled for found records.
     */
    protected bool $nativeCastEnabled = true;

    /**
     * The count of self joins.
     */
    protected static int $selfJoinCount = 0;

    /**
     * Creates instance of the relation.
     */
    public function __construct(QueryBuilder $query, Model $related, Model $parent, ?string $name = null)
    {
        $this->name = $name;
        $this->query = $query->from($related->getTable());
        $this->parent = $parent;
        $this->related = $related;
        $this->connection = $query->getConnection();
    }

    /**
     * {@inheritdoc}
     */
    public function enableNativeCast(): self
    {
        $this->nativeCastEnabled = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function disableNativeCast(): self
    {
        $this->nativeCastEnabled = false;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isNativeCastEnabled(): bool
    {
        return $this->nativeCastEnabled;
    }

    /**
     * {@inheritdoc}
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): Model
    {
        return $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function getRelated(): Model
    {
        return $this->related;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery(): QueryBuilder
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function getExistenceQuery(QueryBuilder $builder, QueryBuilder $parentBuilder, array $columns = ['*']): QueryBuilder
    {
        return $builder
            ->select($columns)
            ->andWhere(
                $builder->expr()->eq(
                    $this->getQualifiedParentKey(),
                    $this->getExistenceCompareKey()
                )
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountQuery(QueryBuilder $builder): QueryBuilder
    {
        return $builder
            ->select([$this->getExistenceCompareKey(), 'COUNT(*) AS AGGREGATE'])
            ->groupBy($this->getExistenceCompareKey())
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getExistenceCountQuery(QueryBuilder $builder, QueryBuilder $parentBuilder): QueryBuilder
    {
        return $this->getExistenceQuery(
            $builder,
            $parentBuilder,
            ['COUNT(*) AS AGGREGATE']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParentKey(): string
    {
        return $this->parent->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getQualifiedParentKey(): string
    {
        return $this->parent->qualifyColumn($this->parent->getPrimaryKey());
    }

    /**
     * {@inheritdoc}
     */
    public function getExistenceCompareKey(): string
    {
        return $this->related->qualifyColumn($this->related->getPrimaryKey());
    }

    /**
     * {@inheritdoc}
     */
    public function getRelationCountHash(bool $incrementJoinCount = true): string
    {
        return 'relation__reserved_' . ($incrementJoinCount ? static::$selfJoinCount++ : static::$selfJoinCount);
    }

    /**
     * {@inheritdoc}
     */
    public function getEager(): Collection
    {
        return $this->get([]);
    }

    /**
     * {@inheritdoc}
     */
    public function getEagerCount(): Collection
    {
        $builder = $this->getCountQuery(clone $this->query);

        return new ArrayCollection(
            $this->getConnection()->fetchAllAssociative(
                $builder->getSQL(),
                $builder->getParameters(),
                (array) $builder->getParameterTypes()
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function get(array $columns = ['*']): Collection
    {
        if (empty($columns) && empty($this->query->getQueryPart('select'))) {
            $columns = ['*'];
        }
        $builder = $this->query->select($columns);
        $records = $this->getConnection()->fetchAllAssociative(
            $builder->getSQL(),
            $builder->getParameters(),
            (array) $builder->getParameterTypes()
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
     * {@inheritdoc}
     */
    abstract public function match(array &$records, Collection $results, string $relation): array;

    /**
     * {@inheritdoc}
     */
    abstract public function addEagerConstraints(array $records): void;

    /**
     * Build model dictionary keyed by the relation's foreign key.
     */
    protected function buildDictionary(Collection $results, string $relationKey): array
    {
        $dictionary = [];
        foreach ($results->map(fn (array $result) => [$result[$relationKey] => $result]) as $pair) {
            $key = key($pair);
            $value = reset($pair);
            if (!isset($dictionary[$key])) {
                $dictionary[$key] = [];
            }
            $dictionary[$key][] = $value;
        }

        return $dictionary;
    }

    /**
     * Get the value of a relationship by one or many type.
     *
     * @param int|string $key
     *
     * @return mixed
     */
    protected function getRelationValue(array $dictionary, $key, string $type)
    {
        if (null === ($value = $dictionary[$key] ?? null)) {
            return null;
        }

        if ('one' === $type) {
            return reset($value);
        }

        return new ArrayCollection($value);
    }

    /**
     * Get all of the primary keys for an array of records.
     */
    protected function getKeys(array $records, string $key): array
    {
        $keys = [];
        foreach ($records as $record) {
            if (null !== ($value = $record[$key] ?? null)) {
                $keys[] = $value;
            }
        }

        if (!empty($keys)) {
            \sort($keys);

            $keys = \array_values(\array_unique($keys));
        }

        return $keys;
    }

    /**
     * Make the name for named query parameter.
     */
    protected function nameParameter(string $name): string
    {
        return ':rp' . \str_replace(' ', '', \ucwords(\str_replace(['-', '_'], ' ', $name)));
    }
}
