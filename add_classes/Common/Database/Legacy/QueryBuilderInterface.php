<?php

declare(strict_types=1);

namespace App\Common\Database\Legacy;

use PDOStatement;

/**
 * Legacy query builder interface.
 *
 * @deprecated
 */
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
    public function getConnection();

    /**
     * Returns the query result.
     */
    public function getQueryResult(): ?PDOStatement;

    /**
     * Returns the last query string.
     */
    public function getLastQuery(): ?string;

    /**
     * Returns the amount of rows that can be affected by insert query.
     */
    public function getAffectableRowsAmount(): int;

    /**
     * Sets the query SELECT.
     */
    public function select(string $columns): void;

    /**
     * Set the query FROM.
     *
     * @param string $table
     */
    public function from($table): void;

    /**
     * Set the query WHERE clause.
     *
     * @param string $predicate
     * @param mixed  $args
     */
    public function where($predicate, $args = null): void;

    /**
     * Set the query OR WHERE clause.
     *
     * @param null|mixed $args
     * @param mixed      $predicate
     */
    public function orWhere($predicate, $args): void;

    /**
     * Set the query raw WHERE clause.
     *
     * @param string     $predicate
     * @param null|mixed $args
     */
    public function whereRaw($predicate, $args = null): void;

    /**
     * Set the query raw OR WHERE clause.
     *
     * @param string     $predicate
     * @param null|mixed $args
     */
    public function orWhereRaw($predicate, $args = null): void;

    /**
     * Set the query HAVING clause.
     *
     * @param string     $predicate
     * @param null|mixed $args
     */
    public function having($predicate, $args = null): void;

    /**
     * Set the query OR HAVING clause.
     *
     * @param string     $predicate
     * @param null|mixed $args
     */
    public function orHaving($predicate, $args = null): void;

    /**
     * Set the query JOIN clause.
     *
     * @param string     $table
     * @param string     $on
     * @param null|mixed $type
     */
    public function join($table, $on, $type = null): void;

    /**
     * Set the query IN clause.
     *
     * @param string $field
     * @param array  $elements
     * @param bool   $list
     */
    public function in($field, $elements, $list = false): void;

    /**
     * Set the query OR IN clause.
     *
     * @param string $field
     * @param array  $elements
     * @param bool   $list
     */
    public function orIn($field, $elements, $list = false): void;

    /**
     * Set the query ORDER BY clause.
     *
     * @param string $clause
     */
    public function orderBy($clause): void;

    /**
     * Set the query GROUP BY clause.
     *
     * @param string $column
     */
    public function groupBy($column): void;

    /**
     * Set the query LIMIT clause.
     *
     * @param int $limit
     * @param int $offset
     */
    public function limit($limit, $offset = 0): void;

    /**
     * Execute the query.
     *
     * @param null|string $query
     * @param null|array  $params
     * @param null|int    $fetchMode
     */
    public function query($query = null, $params = null, $fetchMode = null);

    /**
     * Execute the query for many records.
     *
     * @param null|string $query
     * @param null|array  $params
     * @param null|int    $fetchMode
     */
    public function queryAll($query = null, $params = null, $fetchMode = null);

    /**
     * Execute the query for one record.
     *
     * @param null|string $query
     * @param null|array  $params
     * @param null|int    $fetchMode
     */
    public function queryOne($query = null, $params = null, $fetchMode = null);

    /**
     * Executes raw query.
     *
     * @param string     $query
     * @param null|array $params
     * @param null|int   $returnType
     * @param null|int   $fetchMode
     */
    public function rawQuery($query, $params = null, $returnType = null, $fetchMode = null);

    /**
     * Executes the update query.
     *
     * @param mixed $table
     * @param array $columns
     */
    public function update($table, $columns);

    /**
     * Executes the insert query.
     *
     * @param string $table
     * @param array  $columns
     */
    public function insert($table, $columns);

    /**
     * Executes the insert query for many records.
     *
     * @param string  $table
     * @param array[] $columns
     */
    public function insertMany($table, $columns): int;

    /**
     * Executes the delete query.
     *
     * @param string     $table
     * @param null|mixed $alias
     */
    public function delete($table, $alias = null);

    /**
     * Executes the select query.
     *
     * @param string   $table
     * @param null|int $limit
     * @param null|int $offset
     */
    public function get($table = null, $limit = null, $offset = null);

    /**
     * Executes the select query or one record.
     *
     * @param string $table
     */
    public function getOne($table = null);
}
