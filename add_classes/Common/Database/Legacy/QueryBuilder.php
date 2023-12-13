<?php

declare(strict_types=1);

namespace App\Common\Database\Legacy;

use Exception;
use PDO;
use PDOException;
use PDOStatement;

/**
 * Legacy query builder.
 *
 * @deprecated
 */
final class QueryBuilder implements QueryBuilderInterface
{
    private const SQL_TYPE_SELECT = 'select';

    private const SQL_TYPE_INSERT = 'insert';

    private const SQL_TYPE_UPDATE = 'update';

    private const SQL_TYPE_DELETE = 'delete';

    private const SQL_TYPE_OTHER = 'other';

    private const SQL_TYPE_NONE = null;

    /**
     * The SQL logger.
     *
     * @var SqlLoggerInterface
     */
    private $logger;

    /**
     * The database connection.
     *
     * @var PDO
     */
    private $connection;

    /**
     * The query parameters.
     *
     * @var string[]|string[][]
     */
    private $queryParams = ['select' => '*'];

    /**
     * The last query.
     *
     * @var string
     */
    private $lastQuery;

    /**
     * The query result handle.
     *
     * @var null|\PDOStatement
     */
    private $queryResult;

    /**
     * Flag that indicates current sql type.
     *
     * @var null|string
     */
    private $sqlType = self::SQL_TYPE_NONE;

    /**
     * Indicates the amount of wors that will be inserted into database.
     *
     * @var int
     */
    private $initalRowsAmount = 0;

    /**
     * Creates instance of query builder.
     */
    public function __construct(PDO $connection, ?SqlLoggerInterface $logger = null)
    {
        $this->logger = $logger;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogRecords(): array
    {
        return $this->logger->getRecords();
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryResult(): ?PDOStatement
    {
        return $this->queryResult;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastQuery(): ?string
    {
        return $this->lastQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function getAffectableRowsAmount(): int
    {
        return $this->initalRowsAmount;
    }

    /**
     * {@inheritdoc}
     */
    public function select($columns): void
    {
        // Set SQL to SELECT
        $this->sqlType = static::SQL_TYPE_SELECT;
        $this->queryParams['select'] = $columns;
    }

    /**
     * {@inheritdoc}
     */
    public function from($table, string $alias = null): void
    {
        $this->queryParams['from'] = null !== $alias ? "{$table} AS {$alias}" : $table;
    }

    /**
     * {@inheritdoc}
     */
    public function where($field, $args = null): void
    {
        if (empty($field)) {
            throw new Exception(sprintf('Where field cannot be empty.'));
        }
        if (!preg_match('![=<>]!', $field)) {
            $field .= '=';
        }

        if (false === strpos($field, '?')) {
            $field .= '?';
        }

        $this->addWhereCondition($field, (array) $args, 'AND');
    }

    /**
     * {@inheritdoc}
     */
    public function whereRaw($field, $args = null): void
    {
        if (empty($field)) {
            throw new Exception(sprintf('Where field cannot be empty.'));
        }

        $this->addWhereCondition($field, (array) $args, 'AND');
    }

    /**
     * {@inheritdoc}
     */
    public function orWhereRaw($field, $args = null): void
    {
        if (empty($field)) {
            throw new Exception(sprintf('Where field cannot be empty.'));
        }

        $this->addWhereCondition($field, (array) $args, 'OR');
    }

    /**
     * {@inheritdoc}
     */
    public function having($field, $args = null): void
    {
        if (empty($field)) {
            throw new Exception(sprintf('Having cannot be empty.'));
        }

        $this->addHavingCondition($field, (array) $args, 'AND');
    }

    /**
     * {@inheritdoc}
     */
    public function orHaving($field, $args = null): void
    {
        if (empty($field)) {
            throw new Exception(sprintf('having cannot be empty'));
        }

        $this->addHavingCondition($field, (array) $args, 'OR');
    }

    /**
     * {@inheritdoc}
     */
    public function orWhere($field, $args): void
    {
        if (empty($field)) {
            throw new Exception(sprintf('Where field cannot be empty.'));
        }

        $this->addWhereCondition($field, (array) $args, 'OR');
    }

    /**
     * {@inheritdoc}
     */
    public function join($table, $on, $type = null): void
    {
        $clause = "JOIN {$table} ON {$on}";

        if (!empty($type)) {
            $clause = $type . ' ' . $clause;
        }

        if (!isset($this->queryParams['join'])) {
            $this->queryParams['join'] = [];
        }

        $this->queryParams['join'][] = $clause;
    }

    /**
     * {@inheritdoc}
     */
    public function in($field, $elements, $list = false): void
    {
        $this->addInCondition($field, $elements, $list, 'AND');
    }

    /**
     * {@inheritdoc}
     */
    public function orIn($field, $elements, $list = false): void
    {
        $this->addInCondition($field, $elements, $list, 'OR');
    }

    /**
     * {@inheritdoc}
     */
    public function orderBy($clause): void
    {
        $this->setClause('orderby', $clause);
    }

    /**
     * {@inheritdoc}
     */
    public function groupBy($column): void
    {
        $this->setClause('groupby', $column);
    }

    /**
     * {@inheritdoc}
     */
    public function limit($limit, $offset = 0): void
    {
        if (!empty($offset)) {
            $this->setClause('limit', sprintf('%d,%d', (int) $offset, (int) $limit));
        } else {
            $this->setClause('limit', sprintf('%d', (int) $limit));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function query($query = null, $params = null, $fetchMode = null)
    {
        if (!isset($query)) {
            $query = $this->assembleQuery($params, $fetchMode);
        }

        return $this->rawQuery($query, $params, static::RETURN_TYPE_NONE, $fetchMode);
    }

    /**
     * {@inheritdoc}
     */
    public function queryAll($query = null, $params = null, $fetchMode = null)
    {
        if (!isset($query)) {
            $query = $this->assembleQuery($params, $fetchMode);
        }

        return $this->rawQuery($query, $params, static::RETURN_TYPE_ALL, $fetchMode);
    }

    /**
     * {@inheritdoc}
     */
    public function queryOne($query = null, $params = null, $fetchMode = null)
    {
        if (!isset($query)) {
            $this->limit(1);
            $query = $this->assembleQuery($params, $fetchMode);
        }

        return $this->rawQuery($query, $params, static::RETURN_TYPE_INIT, $fetchMode);
    }

    /**
     * {@inheritdoc}
     */
    public function rawQuery($query, $params = null, $returnType = null, $fetchMode = null)
    {
        // if no fetch mode, use default
        if (!isset($fetchMode)) {
            $fetchMode = PDO::FETCH_ASSOC;
        }

        // Resolve SQL type if not set
        if (static::SQL_TYPE_NONE === $this->sqlType) {
            list($command) = explode(' ', trim($query));
            switch (mb_strtoupper($command)) {
                case 'SELECT':
                    $this->sqlType = static::SQL_TYPE_SELECT;

                    break;
                case 'INSERT':
                    $this->sqlType = static::SQL_TYPE_INSERT;

                    break;
                case 'UPDATE':
                    $this->sqlType = static::SQL_TYPE_UPDATE;

                    break;
                case 'DELETE':
                    $this->sqlType = static::SQL_TYPE_DELETE;

                    break;

                default:
                    $this->sqlType = static::SQL_TYPE_OTHER;

                    break;
            }
        }

        // Set timer
        $start_time = microtime(true);
        // prepare the query
        try {
            $this->queryResult = $this->connection->prepare($query);
            $this->lastQuery = $query;
        } catch (PDOException $exception) {
            // Log query
            $this->logger->log($query, $params, $this->sqlType, $fetchMode, $start_time, false, false, $exception);

            throw new Exception(sprintf('PDO Error: %s Query: %s', $exception->getMessage(), $query));

            return false;
        }

        // execute with params
        try {
            $this->queryResult->execute($params);
        } catch (PDOException $exception) {
            // Log query
            $this->logger->log($query, $params, $this->sqlType, $fetchMode, $start_time, true, false, $exception);

            throw new Exception(sprintf('PDO Error: %s Query: %s', $exception->getMessage(), $query));

            return false;
        }

        // Log query
        $this->logger->log($query, $params, $this->sqlType, $fetchMode, $start_time, true, true);
        // Reset SQL type
        $this->sqlType = static::SQL_TYPE_NONE;

        // get result with fetch mode
        $this->queryResult->setFetchMode($fetchMode);

        switch ($returnType) {
            case static::RETURN_TYPE_INIT:
                return $this->queryResult->fetch();

                break;
            case static::RETURN_TYPE_ALL:
                return $this->queryResult->fetchAll();

                break;
            case static::RETURN_TYPE_NONE:
            default:
                return true;

            break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update($table, $columns)
    {
        if (empty($table)) {
            throw new Exception('Unable to update, table name required');

            return false;
        }
        if (empty($columns) || !is_array($columns)) {
            throw new Exception('Unable to update, at least one column required');

            return false;
        }

        // Set SQL to UPDATE
        $this->sqlType = static::SQL_TYPE_UPDATE;

        $query = ["UPDATE {$table} SET"];
        $fields = [];
        $params = [];
        foreach ($columns as $cname => $cvalue) {
            if (!empty($cname)) {
                $fields[] = "{$cname}=?";
                $params[] = $cvalue;
            }
        }
        $query[] = implode(',', $fields);

        // assemble where clause
        if ($this->assembleWhere($whereString, $whereParams)) {
            $query[] = $whereString;
            $params = array_merge($params, $whereParams);
        }

        $query = implode(' ', $query);

        $this->queryParams = ['select' => '*'];

        return $this->rawQuery($query, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function insert($table, $columns)
    {
        if (empty($table)) {
            throw new Exception('Unable to insert, table name required');

            return false;
        }
        if (empty($columns) || !is_array($columns)) {
            throw new Exception('Unable to insert, at least one column required');

            return false;
        }

        // Set SQL to INSERT
        $this->sqlType = static::SQL_TYPE_INSERT;

        $columnNames = array_keys($columns);
        $query = [sprintf("INSERT INTO `{$table}` (`%s`) VALUES", implode('`,`', $columnNames))];
        $fields = [];
        $params = [];
        foreach ($columns as $cname => $cvalue) {
            if (!empty($cname)) {
                $fields[] = '?';
                $params[] = $cvalue;
            }
        }
        $query[] = '(' . implode(',', $fields) . ')';
        $query = implode(' ', $query);

        $this->rawQuery($query, $params);

        return $this->connection->lastInsertId();
    }

    /**
     * {@inheritdoc}
     */
    public function insertMany($table, $columns): int
    {
        if (empty($table)) {
            throw new Exception('Unable to insert, table name required');

            return false;
        }
        if (empty($columns) || !is_array($columns)) {
            throw new Exception('Unable to insert, at least one column required');

            return false;
        }
        if (empty($columns[0]) || !is_array($columns[0])) {
            throw new Exception('Unable to insert, at least one column required in data arrays');

            return false;
        }

        // Set SQL to INSERT
        $this->sqlType = static::SQL_TYPE_INSERT;

        $column_names = array_keys($columns[0]);

        $query = [sprintf("INSERT INTO `{$table}` (`%s`) VALUES", implode('`,`', $column_names))];
        $fields = [];
        $params = [];
        foreach ($columns as $cname => $cvalue) {
            if (is_array($cvalue) && !empty($cvalue)) {
                $elements = [];
                foreach ($cvalue as $celement) {
                    $elements[] = '?';
                    $params[] = $celement;
                }
                $fields[] = ' ( ' . implode(',', $elements) . ' ) ';
            }
        }
        $query[] = implode(',', $fields);
        $query = implode(' ', $query);

        $this->initalRowsAmount = count($fields);
        $this->rawQuery($query, $params);

        return $this->queryResult->rowCount();
    }

    /**
     * {@inheritdoc}
     */
    public function delete($table, $alias = null)
    {
        if (empty($table)) {
            throw new Exception('Unable to delete, table name required');

            return false;
        }

        // Set SQL to DELETE
        $this->sqlType = static::SQL_TYPE_DELETE;

        if (null === $alias) {
            $query = ["DELETE FROM `{$table}`"];
        } else {
            $query = ["DELETE `{$alias}` FROM `{$table}` `{$alias}`"];
        }

        $params = [];
        // assemble where clause
        if ($this->assembleWhere($whereString, $whereParams)) {
            $query[] = $whereString;
            $params = array_merge($params, $whereParams);
        }
        $query = implode(' ', $query);

        $this->queryParams = ['select' => '*'];

        return $this->rawQuery($query, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function get($table = null, $limit = null, $offset = null)
    {
        if (empty($table) && empty($this->queryParams['from'])) {
            throw new Exception('Unable to fetch data, table name required');
        }

        // Set SQL to SELECT
        $this->sqlType = static::SQL_TYPE_SELECT;

        if (null !== $table) {
            $this->from($table);
        }
        $params = [];
        if (null !== $limit) {
            $this->limit((int) $limit, (int) $offset);
        }

        return $this->rawQuery($this->assembleQuery($params), $params, static::RETURN_TYPE_ALL);
    }

    /**
     * {@inheritdoc}
     */
    public function getOne($table = null)
    {
        if (empty($table) && empty($this->queryParams['from'])) {
            throw new Exception('Unable to fetch data, table name required');
        }

        // Set SQL to SELECT
        $this->sqlType = static::SQL_TYPE_SELECT;

        if (null !== $table) {
            $this->from($table);
        }
        $params = [];
        $this->limit(1);

        return $this->rawQuery($this->assembleQuery($params), $params, static::RETURN_TYPE_INIT);
    }

    /**
     * Sets the WHERE condition.
     *
     * @param string $clause
     * @param mixed  $args
     * @param mixed  $prefix
     */
    private function addWhereCondition($clause, $args = [], $prefix = 'AND')
    {
        // sanity check
        if (empty($clause)) {
            return false;
        }

        // make sure number of ? match number of args
        if (($count = substr_count($clause, '?')) && (count($args) != $count)) {
            throw new Exception(sprintf("Number of where clause args don't match number of ?: '%s'", $clause));
        }
        if (!isset($this->queryParams['where'])) {
            $this->queryParams['where'] = [];
        }

        return $this->queryParams['where'][] = ['clause'=> $clause, 'args'=> $args, 'prefix'=> $prefix];
    }

    /**
     * Sets the HAVING condition.
     *
     * @param string $clause
     * @param mixed  $args
     * @param mixed  $prefix
     */
    private function addHavingCondition($clause, $args = [], $prefix = 'AND')
    {
        // sanity check
        if (empty($clause)) {
            return false;
        }

        // make sure number of ? match number of args
        if (($count = substr_count($clause, '?')) && (count($args) != $count)) {
            throw new Exception(sprintf("Number of having clause args don't match number of ?: '%s'", $clause));
        }
        if (!isset($this->queryParams['having'])) {
            $this->queryParams['having'] = [];
        }

        return $this->queryParams['having'][] = ['clause'=> $clause, 'args'=> $args, 'prefix'=> $prefix];
    }

    /**
     * Sets the IN statement.
     *
     * @param mixed $field
     * @param mixed $elements
     * @param mixed $list
     * @param mixed $prefix
     */
    private function addInCondition($field, $elements, $list = false, $prefix = 'AND')
    {
        if (!$list) {
            if (!is_array($elements)) {
                $elements = explode(',', $elements);
            }

            // quote elements for query
            foreach ($elements as $idx => $element) {
                $elements[$idx] = $this->connection->quote((string) $element);
            }

            $clause = sprintf("{$field} IN (%s)", implode(',', $elements));
        } else {
            $clause = sprintf("{$field} IN (%s)", $elements);
        }

        $this->addWhereCondition($clause, [], $prefix);
    }

    /**
     * Set a query clause.
     *
     * @param string $clause
     * @param mixed  $type
     * @param mixed  $args
     */
    private function setClause($type, $clause, $args = [])
    {
        // sanity check
        if (empty($type) || empty($clause)) {
            return false;
        }

        $this->queryParams[$type] = ['clause'=>$clause];

        if (isset($args)) {
            $this->queryParams[$type]['args'] = $args;
        }
    }

    /**
     * Aeembles the SQL query.
     *
     * @param string $fetchMode the PDO fetch mode
     * @param mixed  $params
     */
    private function assembleQuery(&$params, $fetchMode = null)
    {
        if (empty($this->queryParams['from'])) {
            throw new Exception('Unable to get(), set from() first');

            return false;
        }

        $query = [];
        $query[] = "SELECT {$this->queryParams['select']}";
        $query[] = "FROM {$this->queryParams['from']}";

        // assemble join clause
        if (!empty($this->queryParams['join'])) {
            foreach ($this->queryParams['join'] as $cjoin) {
                $query[] = $cjoin;
            }
        }

        // assemble where clause
        if ($where = $this->assembleWhere($where_string, $params)) {
            $query[] = $where_string;
        }

        // assemble groupby clause
        if (!empty($this->queryParams['groupby'])) {
            $query[] = "GROUP BY {$this->queryParams['groupby']['clause']}";
        }

        // assemble having clause
        if ($having = $this->assembleHaving($having_string, $params)) {
            $query[] = $having_string;
        }

        // assemble orderby clause
        if (!empty($this->queryParams['orderby'])) {
            $query[] = "ORDER BY {$this->queryParams['orderby']['clause']}";
        }

        // assemble limit clause
        if (!empty($this->queryParams['limit'])) {
            $query[] = "LIMIT {$this->queryParams['limit']['clause']}";
        }

        $query_string = implode(' ', $query);
        $this->lastQuery = $query_string;

        $this->queryParams = ['select' => '*'];

        return $query_string;
    }

    /**
     * Assembles the WHERE condition.
     *
     * @param mixed $where
     * @param mixed $params
     */
    private function assembleWhere(&$where, &$params)
    {
        if (!empty($this->queryParams['where'])) {
            $where_init = false;
            $where_parts = [];
            $params = [];
            foreach ($this->queryParams['where'] as $cwhere) {
                $prefix = !$where_init ? 'WHERE' : $cwhere['prefix'];
                $where_parts[] = "{$prefix} {$cwhere['clause']}";
                $params = array_merge($params, (array) $cwhere['args']);
                $where_init = true;
            }
            $where = implode(' ', $where_parts);

            return true;
        }

        return false;
    }

    /**
     * Assembles the HAVING condition.
     *
     * @param mixed $having
     * @param mixed $params
     */
    private function assembleHaving(&$having, &$params)
    {
        if (!empty($this->queryParams['having'])) {
            $having_init = false;
            $having_parts = [];
            $params = [];
            foreach ($this->queryParams['having'] as $chaving) {
                $prefix = !$having_init ? 'HAVING' : $chaving['prefix'];
                $having_parts[] = "{$prefix} {$chaving['clause']}";
                $params = array_merge($params, (array) $chaving['args']);
                $having_init = true;
            }
            $having = implode(' ', $having_parts);

            return true;
        }

        return false;
    }
}
