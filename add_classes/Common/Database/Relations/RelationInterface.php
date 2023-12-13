<?php

declare(strict_types=1);

namespace App\Common\Database\Relations;

use App\Common\Database\Model;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

interface RelationInterface extends CastAwareInterface
{
    /**
     * Set the name of the relation.
     */
    public function setName(string $name): self;

    /**
     * Get the name of the relation.
     */
    public function getName(): ?string;

    /**
     * Get the the parent model.
     */
    public function getParent(): Model;

    /**
     * Get the the related model.
     */
    public function getRelated(): Model;

    /**
     * Get the databse connection.
     */
    public function getConnection(): Connection;

    /**
     * Get the query for relation.
     */
    public function getQuery(): QueryBuilder;

    /**
     * Add the constraints for an internal relationship existence query.
     *
     * @param string[] $columns
     */
    public function getExistenceQuery(QueryBuilder $builder, QueryBuilder $parentBuilder, array $columns = ['*']): QueryBuilder;

    /**
     * Add the constraints for a relationship count query.
     */
    public function getCountQuery(QueryBuilder $builder): QueryBuilder;

    /**
     * Add the constraints for a relationship count query.
     */
    public function getExistenceCountQuery(QueryBuilder $builder, QueryBuilder $parentBuilder): QueryBuilder;

    /**
     * Get the parent key name.
     */
    public function getParentKey(): string;

    /**
     * Get the fully qualified parent key name.
     */
    public function getQualifiedParentKey(): string;

    /**
     * Get the fully qualified related key name.
     */
    public function getExistenceCompareKey(): string;

    /**
     * Get a relationship join table hash.
     */
    public function getRelationCountHash(bool $incrementJoinCount = true): string;

    /**
     * Get the relationship for eager loading.
     */
    public function getEager(): Collection;

    /**
     * Get the relationship for eager counts.
     */
    public function getEagerCount(): Collection;

    /**
     * Execute the query as a "select" statement.
     */
    public function get(array $columns = ['*']): Collection;

    /**
     * Match the eagerly loaded results to their parents.
     */
    public function match(array &$records, Collection $results, string $relation): array;

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @throws RelationEmptyKeysException if keys are empty
     */
    public function addEagerConstraints(array $records): void;
}
