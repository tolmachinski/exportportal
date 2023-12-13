<?php

/*
 * Name:       TinyMVC
 * About:      An MVC application framework for PHP
 * Copyright:  (C) 2007-2008 Monte Ohrt, All rights reserved.
 * Author:     Monte Ohrt, monte [at] ohrt [dot] com
 * License:    LGPL, see included license file
 */

use App\Common\Database\Connection\ConnectionProviderInterface;
use App\Common\Database\Connection\ConnectionProviderTrait;
use App\Common\Database\QueryBuilderAdapter;
use App\Common\Database\QueryBuilderInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDO\Statement as PDOStatement;
use Doctrine\DBAL\Driver\Statement as DriverStatement;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Statement;

/**
 * TinyMVC_PDO.
 *
 * PDO database access
 * compile PHP with --enable-pdo (default with PHP 5.1+)
 *
 * @author		Monte Ohrt
 *
 * @internal Do not use this class directly. Use TinyMVC_PDO::getConnection() instead.
 */
class TinyMVC_PDO implements ConnectionProviderInterface
{
    use ConnectionProviderTrait;

    /**
     * The query builder.
     *
     * @var QueryBuilderInterface
     */
    private $queryBuilder;

    /**
     * Flag that indicates if debug mode is active.
     *
     * @var bool
     */
    private $isDebugMode;

    /**
     * Class constructor.
     */
    public function __construct(Connection $connection, bool $isDebug = false)
    {
        $this->connection = $connection;
        $this->queryBuilder = new QueryBuilderAdapter($connection, $connection->createQueryBuilder());
        $this->isDebugMode = $isDebug;
    }

    /**
     * Class destructor.
     */
    public function __destruct()
    {
        $this->connection = null;
        $this->queryBuilder = null;
    }

    /**
     * Creates the DBAL query builder.
     */
    public function createQueryBuilder(): QueryBuilder
    {
        return $this->getConnection()->createQueryBuilder();
    }

    /**
     * Excecute query builder.
     *
     * @return null|int|PDOStatement|Statement
     *
     * @deprecated use \Doctrine\DBAL\Connection::executeQuery() or query builder instead
     * @see \Doctrine\DBAL\Connection::executeQuery()
     */
    public function execute(QueryBuilder $queryBuilder)
    {
        // In order to prevent BC breaks the query builder will be called by the
        // query builder wrapper
        // @todo Remove this part after full migration to the Doctrine DBAl
        $builderAdapter = $this->getGenericQueryBuilder();
        $builderAdapter->executeQuery(
            $queryBuilder->getSQL(),
            $queryBuilder->getParameters(),
            (array) $queryBuilder->getParameterTypes(),
            true
        );

        return $builderAdapter->getQueryResult();
    }

    /**
     * Returns the query result.
     *
     * @return null|int|PDOStatement|Statement
     *
     * @deprecated use self::createQueryBuilder() instead
     * @see self::createQueryBuilder()
     */
    public function getQueryResult()
    {
        return $this->queryBuilder->getQueryStatement();
    }

    /**
     * Returns the amount of rows that will be affectd by INSERT query.
     *
     * @deprecated do not use it at the any circumstances
     */
    public function getAffectableRowsAmount(): int
    {
        return $this->queryBuilder->getAffectableRowsAmount();
    }

    /**
     * Enables/disables the debug mode.
     *
     * @param bool $mode
     *
     * @deprecated debug mode must be controller by environment
     */
    public function debug($mode)
    {
        $this->isDebugMode = (bool) $mode;
    }

    /**
     * Sets the query SELECT.
     *
     * @param string $clause
     *
     * @deprecated use \Doctrine\DBAL\Query\QueryBuilder::select() instead
     * @see \Doctrine\DBAL\Query\QueryBuilder::select()
     */
    public function select($clause)
    {
        return $this->queryBuilder->select((string) $clause);
    }

    /**
     * Set the query FROM.
     *
     * @param string $clause
     *
     * @deprecated \Doctrine\DBAL\Query\QueryBuilder::from() instead
     * @see \Doctrine\DBAL\Query\QueryBuilder::from()
     */
    public function from($clause)
    {
        return $this->queryBuilder->from(trim((string) $clause));
    }

    /**
     * Set the query WHERE clause.
     *
     * @param string $clause
     * @param mixed  $args
     *
     * @deprecated use \Doctrine\DBAL\Query\QueryBuilder::where() instead
     * @see \Doctrine\DBAL\Query\QueryBuilder::where()
     */
    public function where($clause, $args = null)
    {
        if (!empty($clause)) {
            if (!preg_match('/[=<>]/', $clause)) {
                $clause .= '=';
            }

            if (false === strpos($clause, '?')) {
                $clause .= '?';
            }
        }

        return $this->queryBuilder->where((string) $clause, (array) $args);
    }

    /**
     * Set the query OR WHERE clause.
     *
     * @param string $clause
     * @param mixed  $args
     *
     * @deprecated use \Doctrine\DBAL\Query\QueryBuilder::orWhere() instead
     * @see \Doctrine\DBAL\Query\QueryBuilder::orWhere()
     */
    public function or_where($clause, $args)
    {
        $this->queryBuilder->orWhere((string) $clause, (array) $args);
    }

    /**
     * Set the query raw WHERE clause.
     *
     * @param string $clause
     * @param mixed  $args
     *
     * @deprecated use \Doctrine\DBAL\Query\QueryBuilder::where() instead
     * @see \Doctrine\DBAL\Query\QueryBuilder::where()
     */
    public function where_raw($clause, $args = null)
    {
        $this->queryBuilder->where((string) $clause, (array) $args);
    }

    /**
     * Set the query raw OR WHERE clause.
     *
     * @param string $clause
     * @param mixed  $args
     *
     * @deprecated use \Doctrine\DBAL\Query\QueryBuilder::orWhere() instead
     * @see \Doctrine\DBAL\Query\QueryBuilder::orWhere()
     */
    public function or_where_raw($clause, $args = null)
    {
        $this->queryBuilder->orWhere((string) $clause, (array) $args);
    }

    /**
     * Set the query HAVING clause.
     *
     * @param string $clause
     * @param mixed  $args
     *
     * @deprecated use \Doctrine\DBAL\Query\QueryBuilder::having() instead
     * @see \Doctrine\DBAL\Query\QueryBuilder::having()
     */
    public function having($clause, $args = null)
    {
        $this->queryBuilder->having((string) $clause, (array) $args);
    }

    /**
     * Set the query OR HAVING clause.
     *
     * @param string $clause
     * @param mixed  $args
     *
     * @deprecated use \Doctrine\DBAL\Query\QueryBuilder::having() instead
     * @see \Doctrine\DBAL\Query\QueryBuilder::having()
     */
    public function or_having($clause, $args = null)
    {
        $this->queryBuilder->orHaving((string) $clause, (array) $args);
    }

    /**
     * Set the query IN clause.
     *
     * @param string       $field
     * @param array|string $elements
     * @param bool         $list
     *
     * @deprecated use \Doctrine\DBAL\Query\QueryBuilder::where() instead
     * @see \Doctrine\DBAL\Query\QueryBuilder::where()
     */
    public function in($field, $elements, $list = false)
    {
        $this->queryBuilder->in((string) $field, $elements, (bool) $list);
    }

    /**
     * Set the query OR IN clause.
     *
     * @param string       $field
     * @param array|string $elements
     * @param bool         $list
     *
     * @deprecated use \Doctrine\DBAL\Query\QueryBuilder::where() instead
     * @see \Doctrine\DBAL\Query\QueryBuilder::where()
     */
    public function or_in($field, $elements, $list = false)
    {
        $this->queryBuilder->orIn((string) $field, $elements, (bool) $list);
    }

    /**
     * Set the query JOIN clause.
     *
     * @param mixed      $joinTable
     * @param mixed      $joinOn
     * @param null|mixed $joinType
     *
     * @deprecated use \Doctrine\DBAL\Query\QueryBuilder::join() instead
     * @see \Doctrine\DBAL\Query\QueryBuilder::join()
     */
    public function join($joinTable, $joinOn, $joinType = null)
    {
        $this->queryBuilder->join((string) $joinTable, (string) $joinOn, null !== $joinType ? (string) $joinType : null);
    }

    /**
     * Set the query ORDER BY clause.
     *
     * @param string $clause
     *
     * @deprecated use \Doctrine\DBAL\Query\QueryBuilder::orderBy() instead
     * @see \Doctrine\DBAL\Query\QueryBuilder::orderBy()
     */
    public function orderby($clause)
    {
        $this->queryBuilder->orderBy((string) $clause);
    }

    /**
     * Set the query GROUP BY clause.
     *
     * @param string $clause
     *
     * @deprecated use \Doctrine\DBAL\Query\QueryBuilder::groupBy() instead
     * @see \Doctrine\DBAL\Query\QueryBuilder::groupBy()
     */
    public function groupby($clause)
    {
        $this->queryBuilder->groupBy((string) $clause);
    }

    /**
     * Set the query LIMIT clause.
     *
     * @param int $limit
     * @param int $offset
     *
     * @deprecated use \Doctrine\DBAL\Query\QueryBuilder::limit() instead
     * @see \Doctrine\DBAL\Query\QueryBuilder::limit()
     */
    public function limit($limit, $offset = 0)
    {
        $this->queryBuilder->limit((int) $limit, (int) $offset ?: null);
    }

    /**
     * Execute the query.
     *
     * @param null|string $query  query string
     * @param null|array  $params an array of query params
     *
     * @deprecated use \Doctrine\DBAL\Connection::executeQuery() or query builder instead
     * @see \Doctrine\DBAL\Connection::executeQuery()
     *
     * @return bool
     */
    public function query($query = null, $params = null)
    {
        if (3 === func_num_args()) {
            trigger_error('The usage of the argument $fetchMode is deprecated. This argument will be ignored.', E_WARNING);
        }

        return $this->queryBuilder->query(
            null !== $query ? (string) $query : null,
            null !== $params ? (array) $params : null
        );
    }

    /**
     * Execute the query for many records.
     *
     * @param null|string $query  query string
     * @param null|array  $params an array of query params
     *
     * @deprecated use \Doctrine\DBAL\Connection::executeQuery() or query builder instead
     * @see \Doctrine\DBAL\Connection::executeQuery()
     *
     * @return mixed[]
     */
    public function query_all($query = null, $params = null)
    {
        if (3 === func_num_args()) {
            trigger_error('The usage of the argument $fetchMode is deprecated. This argument will be ignored.', E_WARNING);
        }

        return $this->queryBuilder->queryAll(
            null !== $query ? (string) $query : null,
            null !== $params ? (array) $params : null
        );
    }

    /**
     * Execute the query for one record.
     *
     * @param null|string $query  query string
     * @param null|array  $params an array of query params
     *
     * @deprecated use \Doctrine\DBAL\Connection::executeQuery() or query builder instead
     * @see \Doctrine\DBAL\Connection::executeQuery()
     *
     * @return mixed
     */
    public function query_one($query = null, $params = null)
    {
        if (3 === func_num_args()) {
            trigger_error('The usage of the argument $fetchMode is deprecated. This argument will be ignored.', E_WARNING);
        }

        return $this->queryBuilder->queryOne(
            null !== $query ? (string) $query : null,
            null !== $params ? (array) $params : null
        );
    }

    /**
     * Executes the raw query.
     *
     * @param string     $query      the query string
     * @param null|array $params     an array of query params
     * @param null|int   $returnType none/all/init
     *
     * @deprecated use \Doctrine\DBAL\Connection::executeQuery() or query builder instead
     * @see \Doctrine\DBAL\Connection::executeQuery()
     *
     * @return bool|mixed|mixed[]
     */
    public function query_raw($query, $params = null, $returnType = QueryBuilderInterface::RETURN_TYPE_NONE)
    {
        if (4 === func_num_args()) {
            trigger_error('The usage of the argument $fetchMode is deprecated. This argument will be ignored.', E_WARNING);
        }

        return $this->queryBuilder->queryRaw(
            null !== $query ? (string) $query : null,
            null !== $params ? (array) $params : null,
            null !== $returnType ? (int) $returnType : null
        );
    }

    /**
     * Executes the UPDATE query.
     *
     * @param string     $table
     * @param null|array $columns
     *
     * @deprecated use \Doctrine\DBAL\Connection::update() or query builder instead
     * @see \Doctrine\DBAL\Connection::update()
     *
     * @return bool
     */
    public function update($table, $columns)
    {
        return $this->queryBuilder->update((string) $table, $columns);
    }

    /**
     * Executes the INSERT query.
     *
     * @param string     $table
     * @param null|array $columns
     *
     * @deprecated use \Doctrine\DBAL\Connection::insert() or query builder instead
     * @see \Doctrine\DBAL\Connection::insert()
     *
     * @return string
     */
    public function insert($table, $columns)
    {
        return $this->queryBuilder->insert((string) $table, $columns);
    }

    /**
     * Executes the INSERT query for many records.
     *
     * @param string       $table
     * @param null|array[] $columns
     *
     * @deprecated use \Doctrine\DBAL\Connection::insert() or query builder instead
     * @see \Doctrine\DBAL\Connection::insert()
     *
     * @return string
     */
    public function insert_batch($table, $columns)
    {
        $this->queryBuilder->insertMany((string) $table, $columns);

        return $this->getConnection()->lastInsertId();
    }

    /**
     * Executes the DELETE query.
     *
     * @param string     $table
     * @param null|mixed $alias
     *
     * @deprecated use \Doctrine\DBAL\Connection::delete() or query builder instead
     * @see \Doctrine\DBAL\Connection::delete()
     *
     * @return bool
     */
    public function delete($table, $alias = null)
    {
        return $this->queryBuilder->delete((string) $table, null !== $alias ? (string) $alias : null);
    }

    /**
     * Executes the SELECT query.
     *
     * @param string $table
     * @param int    $limit
     * @param int    $offset
     *
     * @deprecated use \Doctrine\DBAL\Connection::executeQuery() or query builder instead
     * @see \Doctrine\DBAL\Connection::executeQuery()
     *
     * @return bool|mixed|mixed[]
     */
    public function get($table = null, $limit = null, $offset = null)
    {
        return $this->queryBuilder->get(
            null !== $table ? (string) $table : null,
            null !== $limit ? (int) $limit : null,
            null !== $offset ? (int) $offset : null
        );
    }

    /**
     * Executes the SELECT query or one record.
     *
     * @param string $table
     *
     * @deprecated use \Doctrine\DBAL\Connection::executeQuery() or query builder instead
     * @see \Doctrine\DBAL\Connection::executeQuery()
     *
     * @return mixed
     */
    public function get_one($table = null)
    {
        return $this->queryBuilder->getOne(null !== $table ? (string) $table : null);
    }

    /**
     * Go to next record in result set.
     *
     * @deprecated use QueryBuilder::execute()->fetchAssociative() instead
     * @see QueryBuilder::execute()->fetchAssociative()
     */
    public function next()
    {
        if (1 === func_num_args()) {
            trigger_error('The usage of the argument $fetchMode is deprecated. This argument will be ignored.', E_WARNING);
        }

        /** @var Statement $result */
        $result = $this->queryBuilder->getQueryStatement();
        if (null === $result) {
            return null;
        }

        return $result->fetchAssociative();
    }

    /**
     * Get last insert id from previous query.
     *
     * @return string
     *
     * @deprecated use \Doctrine\DBAL\Connection::lastInsertId() instead
     * @see \Doctrine\DBAL\Connection::lastInsertId()
     */
    public function last_insert_id()
    {
        return $this->getConnection()->lastInsertId();
    }

    /**
     * Get number of returned rows from previous select.
     *
     * @return int
     *
     * @deprecated use \Doctrine\DBAL\Driver\Statement::rowCount() instead
     * @see \Doctrine\DBAL\Driver\Statement::rowCount()
     *
     * @uses self::affectedRows()
     */
    public function numRows()
    {
        return $this->affectedRows();
    }

    /**
     * Fet number of affected rows from previous insert/update/delete.
     *
     * @return int
     *
     * @deprecated use \Doctrine\DBAL\Driver\Statement::rowCount() instead
     * @see \Doctrine\DBAL\Driver\Statement::rowCount()
     */
    public function affectedRows()
    {
        $rows = 0;
        $result = $this->queryBuilder->getQueryResult();
        if ($result instanceof DriverStatement || $result instanceof Statement) {
            $rows = $result->rowCount();
        } else {
            $rows = (int) $result;
        }

        return $rows;
    }

    /**
     * Return last executed query.
     *
     * @return string
     *
     * @deprecated use logger instead
     */
    public function lastQuery()
    {
        return $this->queryBuilder->getLastQuery();
    }

    /**
     * Returns the list of exected queries if debug mode is enabled.
     *
     * @return array
     *
     * @deprecated use \Doctrine\DBAL\Configuration::getSQLLogger() instead
     * @see \Doctrine\DBAL\Configuration::getSQLLogger()
     */
    public function queryLog()
    {
        return $this->isDebugMode ? $this->queryBuilder->getLogRecords() : [];
    }

    /**
     * Returns the default query builder.
     *
     * @deprecated use QueryBuilder::class instance instead
     * @see self::createQueryBuilder() To create the query builder
     */
    protected function getGenericQueryBuilder(): QueryBuilderInterface
    {
        return $this->queryBuilder;
    }
}
