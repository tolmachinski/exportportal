<?php

declare(strict_types=1);

namespace App\Common\Database;

use Doctrine\DBAL\Query\QueryBuilder;
use IteratorAggregate;
use TinyMVC_Model as CoreModel;

/**
 * App model class.
 *
 * @author Anton Zencenco
 *
 * @deprecated
 * @see \App\Common\Database\Model
 */
abstract class BaseModel extends CoreModel
{
    use Concerns\HasScopes;
    use Concerns\HasConnection;
    use Concerns\HasRelationships;

    /**
     * Creates the DBAL query builder.
     */
    public function createQueryBuilder(): QueryBuilder
    {
        return $this->getHandler()->createQueryBuilder();
    }

    /**
     * Removes the records by provided parameters.
     */
    protected function removeRecords(
        ?string $section,
        string $table,
        ?string $alias = null,
        array $params = []
    ): bool {
        unset($params['table'], $params['alias'], $params['section']);
        extract($params);

        // Create builder
        $builder = $this->createQueryBuilder()
            ->delete($table, $alias)
        ;

        // Resolve scopes
        $this->addScopedConstraints($builder, $section, $scopes ?? $conditions ?? []);
        // Add relation existence queries
        $this->attachExistenceRelations($builder, $exists ?? [], $section);
        $builder->execute();

        return true;
    }

    /**
     * Find set of records by provided parameters.
     */
    protected function findRecords(?string $section, string $table, ?string $alias = null, array $params = []): array
    {
        unset($params['table'], $params['alias'], $params['section']);
        extract($params);

        // Create builder
        $builder = $this->createQueryBuilder()
            ->select(...(array) ($columns ?? '*'))
            ->from($table, $alias)
        ;

        /* @todo Resotore in the `v2.42`
        // Add `withCount` to the query.
        $this->addWithCountRelations(
            $builder,
            $with_count ?? [],
            $section
        );
        */

        // Resolve bindings
        $this->addBindingConstraints($builder, $section, $joins ?? []);
        // Resolve scopes
        $this->addScopedConstraints($builder, $section, $scopes ?? $conditions ?? []);
        // Add relation existence queries
        $this->attachExistenceRelations($builder, $exists ?? [], $section);
        // Apply standard scopes
        $this->scopeGroupBy($builder, $group ?? []);
        $this->scopeOrderBy($builder, $order ?? []);
        $this->scopeLimitOrSkip($builder, $limit ?? null, $skip ?? null);

        // Fetch records
        $records = $this->getConnection()->fetchAllAssociative(
            $builder->getSQL(),
            $builder->getParameters(),
            (array) $builder->getParameterTypes()
        );
        if (empty($records)) {
            return [];
        }

        // @todo Remove in the `v2.42`
        $records = $this->loadEagerRelations(
            $this->parseRelations($with ?? []),
            $records,
            $section
        );

        // @todo Remove in the `v2.42`
        return $this->loadEagerCountRelations(
            $this->parseRelations($with_count ?? []),
            $records,
            $section
        );

        /* @todo Resotore in the `v2.42`
        // Load eager relationships
        return $this->addNestedWithRelations(
            $this->parseNestedRelations($with ?? [], $section),
            $records
        );
        */
    }

    /**
     * Find one record by provided parameters.
     *
     * @param null|mixed $primaryKeyName
     * @param null|mixed $primaryKey
     */
    protected function findRecord(
        ?string $section,
        string $table,
        ?string $alias = null,
        $primaryKeyName = null,
        $primaryKey = null,
        array $params = []
    ): ?array {
        unset($params['table'], $params['alias'], $params['section']);
        extract($params);

        // Create builder
        $builder = $this->createQueryBuilder()
            ->select(...(array) ($columns ?? '*'))
            ->from($table, $alias)
        ;

        /* @todo Resotore in the `v2.42`
        // Add `withCount` to the query.
        $this->addWithCountRelations(
            $builder,
            $with_count ?? [],
            $section
        );
        */

        // Resolve bindings
        $this->addBindingConstraints($builder, $section, $joins ?? []);
        // Resolve scopes
        $this->scopePrimaryKey($builder, $alias ?? $table, $primaryKeyName, $primaryKey);
        $this->addScopedConstraints($builder, $section, $scopes ?? $conditions ?? []);
        // Add relation existence queries
        $this->attachExistenceRelations($builder, $exists ?? [], $section);
        // Apply standard scopes
        $this->scopeGroupBy($builder, $group ?? []);
        $this->scopeOrderBy($builder, $order ?? []);
        $this->scopeLimitOrSkip($builder, 1, $skip ?? null);

        // Fetch records
        $records = [
            $record = $this->getConnection()->fetchAssociative(
                $builder->getSQL(),
                $builder->getParameters(),
                (array) $builder->getParameterTypes()
            ),
        ];
        if (empty($record)) {
            return null;
        }

        // @todo Remove in the `v2.42`
        $records = $this->loadEagerRelations(
            $this->parseRelations($with ?? []),
            $records,
            $section
        );

        // @todo Remove in the `v2.42`
        $records = $this->loadEagerCountRelations(
            $this->parseRelations($with_count ?? []),
            $records,
            $section
        );

        /* @todo Resotore in the `v2.42`
        // Load eager relationships
        $records = $this->addNestedWithRelations(
            $this->parseNestedRelations($with ?? [], $section),
            $records
        );
        */

        return $records[0] ?? null;
    }

    /**
     * Update set of records by provided parameters.
     */
    protected function updateRecords(?string $section, string $table, ?string $alias = null, array $record, array $params = []): int
    {
        unset($params['table'], $params['alias'], $params['section']);
        extract($params);

        // Create builder
        $builder = $this->createQueryBuilder()
            ->update($table, $alias)
        ;
        // @todo add support for UPDATE JOIN
        // Resolve bindings
        $this->addBindingConstraints($builder, $section, $joins ?? []);
        // Resolve scopes
        $this->addScopedConstraints($builder, $section, $scopes ?? $conditions ?? []);
        // Add relation existence queries
        $this->attachExistenceRelations($builder, $exists ?? [], $section);
        // Fill builder with data
        foreach ($record as $column => $value) {
            $builder->set($column, $builder->createNamedParameter($value, null, ":{$column}"));
        }

        // Update records
        return $builder->execute() ?? 0;
    }

    /**
     * Transforms provided {@link $columns} variable into a valid string.
     *
     * @param null|array|string $columns comma-separated string or array of column names
     */
    protected function prepareColumns($columns): string
    {
        if (null === $columns || empty($columns)) {
            return '*';
        }

        if (!(is_string($columns) || is_array($columns))) {
            $current_type = gettype($columns);

            throw new \InvalidArgumentException(
                "Invalid argument for column projection provided - string or array expected, got {$current_type}"
            );
        }

        return is_string($columns) ? $columns : implode(', ', $columns);
    }

    /**
     * Scope a query to be filtered by the primary key.
     *
     * @param string|string[] $primaryKey
     * @param mixed           $keyValue
     */
    protected function scopePrimaryKey(QueryBuilder $builder, ?string $table, $primaryKey, $keyValue): void
    {
        if (empty($primaryKey)) {
            return;
        }

        if (is_array($primaryKey)) {
            if (!is_array($keyValue)) {
                $keyValue = array_fill(0, count($primaryKey), $keyValue);
            } elseif (count($primaryKey) !== count($keyValue)) {
                return;
            }

            $builder->andWhere(
                ...array_map(
                    fn ($column, $value): string => $builder->expr()->eq(
                        \ltrim("`{$table}`.`{$column}`", '.'),
                        $builder->createNamedParameter($value, null, $this->nameScopeParameter('primaryKey' . $column))
                    ),
                    array_values($primaryKey),
                    $keyValue
                )
            );
        } elseif (is_string($primaryKey)) {
            $builder->andWhere(
                $builder->expr()->eq(
                    \ltrim("`{$table}`.`{$primaryKey}`", '.'),
                    $builder->createNamedParameter($keyValue, null, $this->nameScopeParameter('primaryKey'))
                )
            );
        }
    }

    /**
     * Scopes query by the filter map where each key is a column in the table.
     *
     * @param Array<string, callble|int|iterator|string> $filters
     */
    protected function scopeFilterBy(QueryBuilder $builder, array $filters): void
    {
        foreach ($filters as $column => $value) {
            if (!\is_string($column)) {
                continue;
            }
            if ($value instanceof IteratorAggregate) {
                $value = \iterator_to_array($value);
            }
            if (\is_callable($value)) {
                $value = $value();
            }

            if (\is_array($value)) {
                $builder->andWhere(
                    $builder->expr()->in(
                        $column,
                        \array_map(
                            fn (int $index, $v) => $builder->createNamedParameter(
                                $v,
                                null,
                                $this->nameScopeParameter(\sprintf('vF%s%s', $index, \bin2hex(\random_bytes(12))))
                            ),
                            \array_keys(\array_values($value)),
                            $value
                        )
                    )
                );
            } else {
                $builder->andWhere(
                    $builder->expr()->eq(
                        $column,
                        $builder->createNamedParameter($value, null, $this->nameScopeParameter(\sprintf('vF%s', \bin2hex(\random_bytes(12)))))
                    )
                );
            }
        }
    }

    /**
     * Scope a query to group by list of columns.
     */
    protected function scopeGroupBy(QueryBuilder $builder, ?array $columns = null): void
    {
        if (!empty($columns)) {
            foreach ($columns as $column) {
                $builder->addGroupBy($column);
            }
        }
    }

    /**
     * Scope a query to be oredered by a list of columns.
     */
    protected function scopeOrderBy(QueryBuilder $builder, ?array $columns = null): void
    {
        if (empty($columns)) {
            return;
        }

        foreach ($columns as $column => $direction) {
            if (is_numeric($column)) {
                $builder->addOrderBy($direction);

                continue;
            }

            if (!empty($direction) && is_string($direction)) {
                $builder->addOrderBy($column, mb_strtoupper($direction));
            } else {
                $builder->addOrderBy($column);
            }
        }
    }

    /**
     * Scope a query to be limit or skip records.
     */
    protected function scopeLimitOrSkip(QueryBuilder $builder, ?int $limit = null, ?int $skip = null): void
    {
        $builder->setMaxResults($limit);
        if (null !== $skip) {
            $builder->setFirstResult($skip);
        }
    }
}
