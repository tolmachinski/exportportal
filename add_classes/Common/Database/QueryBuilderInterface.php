<?php

declare(strict_types=1);

namespace App\Common\Database;

use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Driver\Statement as DriverStatement;

interface QueryBuilderInterface
{
    public const RETURN_TYPE_NONE = 0;

    public const RETURN_TYPE_INIT = 1;

    public const RETURN_TYPE_ALL = 2;

    /**
     * Returns the records from internal logger.
     */
    public function getLogRecords(): array;

    /**
     * Returns the connection.
     */
    public function getConnection(): DriverConnection;

    /**
     * Returns the query result.
     *
     * @return null|DriverStatement|int
     */
    public function getQueryResult();

    /**
     * Returns the query statement.
     */
    public function getQueryStatement(): ?DriverStatement;

    /**
     * Returns the last query string.
     */
    public function getLastQuery(): ?string;

    /**
     * Returns the amount of rows that can be affected by insert query.
     */
    public function getAffectableRowsAmount(): int;

    /**
     * Returns the SQL query string.
     */
    public function getSQL(): string;

    /**
     * Sets the query SELECT.
     */
    public function select(string $columns): void;

    /**
     * Set the query FROM.
     */
    public function from(string $table, ?string $alias = null): void;

    /**
     * Set the query WHERE clause.
     */
    public function where(string $predicate, ?array $args = null): void;

    /**
     * Set the query OR WHERE clause.
     */
    public function orWhere(string $predicate, ?array $args): void;

    /**
     * Set the query HAVING clause.
     */
    public function having(string $predicate, ?array $args = null): void;

    /**
     * Set the query OR HAVING clause.
     */
    public function orHaving(string $field, ?array $args = null): void;

    /**
     * Set the query IN clause.
     *
     * @param array|string $elements
     */
    public function in(string $field, $elements, bool $list = false): void;

    /**
     * Set the query OR IN clause.
     *
     * @param array|string $elements
     */
    public function orIn(string $field, $elements, bool $list = false): void;

    /**
     * Set the query JOIN clause.
     */
    public function join(string $table, string $on, ?string $type = null): void;

    /**
     * Set the query ORDER BY clause.
     */
    public function orderBy(?string $sort): void;

    /**
     * Set the query GROUP BY clause.
     */
    public function groupBy(?string $groupBy): void;

    /**
     * Set the query LIMIT clause.
     */
    public function limit(?int $limit, ?int $offset = 0): void;

    /**
     * Execute the query.
     */
    public function query(?string $query = null, ?array $params = null): bool;

    /**
     * Execute the query for one record.
     *
     * @return mixed
     */
    public function queryOne(?string $query = null, ?array $params = null);

    /**
     * Execute the query for many records.
     *
     * @return mixed[]
     */
    public function queryAll(?string $query = null, ?array $params = null);

    /**
     * Executes raw query.
     *
     * @return bool|mixed|mixed[]
     */
    public function queryRaw(string $query, ?array $params = null, ?int $returnType = self::RETURN_TYPE_NONE);

    /**
     * Executes the select query.
     *
     * @return bool|mixed|mixed[]
     */
    public function get(?string $table = null, ?int $limit = null, ?int $offset = null);

    /**
     * Executes the select query or one record.
     *
     * @return mixed
     */
    public function getOne(?string $table = null);

    /**
     * Executes the insert query.
     */
    public function insert(string $table, ?array $columns): string;

    /**
     * Executes the insert query for many records.
     */
    public function insertMany(string $table, ?array $entries): int;

    /**
     * Executes the update query.
     */
    public function update(string $table, ?array $columns): bool;

    /**
     * Executes the delete query.
     */
    public function delete(string $table, ?string $alias = null): bool;

    /**
     * Executes the query.
     */
    public function executeQuery(string $sql, array $params = [], array $types = [], bool $isRaw = false): void;

    /**
     * Resets the query.
     */
    public function reset(): void;
}
