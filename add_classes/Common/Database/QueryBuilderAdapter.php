<?php

declare(strict_types=1);

namespace App\Common\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Driver\PDO\Statement as PDOStatement;
use Doctrine\DBAL\Driver\Statement as DriverStatement;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Statement;
use Exception;
use function Symfony\Component\String\u;

final class QueryBuilderAdapter implements QueryBuilderInterface
{
    /**
     * The last query.
     *
     * @var string
     */
    private $lastQuery;

    /**
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * The query statement.
     *
     * @var null|PDOStatement|Statement
     */
    private $queryStatement;

    /**
     * The query result handle.
     *
     * @var null|int|PDOStatement|Statement
     */
    private $queryResult;

    /**
     * The query builder.
     *
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * Indicates the amount of wors that will be inserted into database.
     *
     * @var int
     */
    private $initalRowsAmount = 0;

    /**
     * The first FROM value. Used to make joins.
     *
     * @var string
     */
    private $fromTable;

    /**
     * The first FROM alias value. Used to make joins.
     *
     * @var string
     */
    private $fromAlias;

    /**
     * Creates instance of query builder.
     */
    public function __construct(Connection $connection, QueryBuilder $queryBuilder)
    {
        $this->connection = $connection;
        $this->queryBuilder = $queryBuilder;
        $this->queryBuilder->select('*');
    }

    /**
     * {@inheritdoc}
     */
    public function getLogRecords(): array
    {
        return $this->connection->getConfiguration()->getSQLLogger()->queries ?? [];
    }

    /**
     * {@inheritdoc}
     *
     * @return Connection
     */
    public function getConnection(): DriverConnection
    {
        return $this->connection;
    }

    /**
     * {@inheritdoc}
     *
     * @return null|int|PDOStatement|Statement
     */
    public function getQueryResult()
    {
        return $this->queryResult;
    }

    /**
     * {@inheritdoc}
     *
     * @return null|PDOStatement|Statement
     */
    public function getQueryStatement(): ?DriverStatement
    {
        return $this->queryStatement;
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
    public function getSQL(): string
    {
        return $this->queryBuilder->getSQL();
    }

    /**
     * {@inheritdoc}
     */
    public function select(string $columns): void
    {
        $this->queryBuilder->select($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function from(string $table, ?string $alias = null): void
    {
        list($tableName, $tableAlias) = $this->decomposeFromExpression($table);
        $table = $tableName ?? $table;
        $alias = $tableAlias ?? $alias;

        if (null === $this->fromTable) {
            $this->fromTable = $table;
        }
        if (null === $this->fromAlias) {
            $this->fromAlias = $alias;
        }

        $this->queryBuilder->from($tableName, ($tableAlias ?? $alias) ?: null);
    }

    /**
     * {@inheritdoc}
     */
    public function where(string $predicate, ?array $args = null): void
    {
        if (empty($predicate)) {
            throw new Exception(sprintf('Where field cannot be empty.'));
        }

        $this->queryBuilder->andWhere($predicate);
        if (null !== $args && !empty($args)) {
            $this->addParameters($args);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function orWhere(string $predicate, ?array $args): void
    {
        if (empty($predicate)) {
            throw new Exception(sprintf('Where field cannot be empty.'));
        }

        $this->queryBuilder->orWhere($predicate);
        if (null !== $args && !empty($args)) {
            $this->addParameters($args);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function having(string $predicate, ?array $args = null): void
    {
        if (empty($predicate)) {
            throw new Exception(sprintf('Having cannot be empty.'));
        }

        $this->queryBuilder->andHaving($predicate);
        if (null !== $args && !empty($args)) {
            $this->addParameters($args);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function orHaving(string $field, ?array $args = null): void
    {
        if (empty($predicate)) {
            throw new Exception(sprintf('Having cannot be empty.'));
        }

        $this->queryBuilder->orHaving($predicate);
        if (null !== $args && !empty($args)) {
            $this->addParameters($args);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function in(string $field, $elements, bool $list = false): void
    {
        list($parameters, $placeholders) = $this->decomposeElementsList($elements);

        $this->addParameters($parameters);
        $this->queryBuilder->andWhere(
            $this->queryBuilder->expr()->in($field, $placeholders)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function orIn(string $field, $elements, bool $list = false): void
    {
        list($parameters, $placeholders) = $this->decomposeElementsList($elements);

        $this->addParameters($parameters);
        $this->queryBuilder->orWhere(
            $this->queryBuilder->expr()->in($field, $placeholders)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function join(string $table, string $on, ?string $type = 'inner'): void
    {
        $type = null !== $type ? \mb_strtolower($type, 'utf-8') : null;
        $fromAlias = $this->fromAlias ?? $this->fromTable;
        list($joinName, $joinAlias) = $this->decomposeFromExpression($table);
        if (empty($joinAlias)) {
            $joinAlias = $joinName;
        }

        $joinAlias = u($joinAlias)->trim('`')->append('`')->prepend('`')->toString();

        switch ($type) {
            case 'left':
                $this->queryBuilder->leftJoin($fromAlias, $joinName, $joinAlias, $on);

                break;
            case 'right':
                $this->queryBuilder->rightJoin($fromAlias, $joinName, $joinAlias, $on);

                break;
            case 'inner':
                $this->queryBuilder->innerJoin($fromAlias, $joinName, $joinAlias, $on);

                break;

            default:
                $this->queryBuilder->add('join', [
                    $fromAlias => [
                        'joinType'      => $type,
                        'joinTable'     => $joinName,
                        'joinAlias'     => $joinAlias,
                        'joinCondition' => $on,
                    ],
                ], true);

                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function orderBy(?string $sort): void
    {
        if (empty($sort)) {
            return;
        }

        $this->queryBuilder->add('orderBy', $sort, false);
    }

    /**
     * {@inheritdoc}
     */
    public function groupBy(?string $groupBy): void
    {
        if (empty($groupBy)) {
            return;
        }

        $this->queryBuilder->add('groupBy', $groupBy, false);
    }

    /**
     * {@inheritdoc}
     */
    public function limit(?int $limit, ?int $offset = 0): void
    {
        $this->queryBuilder->setMaxResults($limit);
        if (!empty($offset)) {
            $this->queryBuilder->setFirstResult($offset);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function query(?string $query = null, ?array $params = null): bool
    {
        $this->executeQueryInCompatMode($query, $params);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function queryOne(?string $query = null, ?array $params = null)
    {
        if (null === $query) {
            $this->limit(1);
        }
        $this->executeQueryInCompatMode($query, $params);

        return $this->queryStatement->fetchAssociative();
    }

    /**
     * {@inheritdoc}
     */
    public function queryAll(?string $query = null, ?array $params = null)
    {
        $this->executeQueryInCompatMode($query, $params);

        return $this->queryStatement->fetchAllAssociative();
    }

    /**
     * {@inheritdoc}
     */
    public function queryRaw(string $query, ?array $params = null, ?int $returnType = self::RETURN_TYPE_NONE)
    {
        $this->executeQueryInCompatMode($query, $params);

        switch ($returnType) {
            case static::RETURN_TYPE_INIT:
                return $this->queryStatement->fetchAssociative();
            case static::RETURN_TYPE_ALL:
                return $this->queryStatement->fetchAllAssociative();
            case static::RETURN_TYPE_NONE:
            default:
                return true;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return bool|mixed|mixed[]
     */
    public function get(?string $table = null, ?int $limit = null, ?int $offset = null)
    {
        if (empty($table) && empty($this->queryBuilder->getQueryPart('from'))) {
            throw new Exception('Unable to fetch data, table name required');
        }

        if (null !== $table) {
            $this->from($table);
        }
        if (null !== $limit) {
            $this->limit((int) $limit, (int) $offset ?: null);
        }
        $this->executeQueryInCompatMode();

        return $this->queryStatement->fetchAllAssociative();
    }

    /**
     * {@inheritdoc}
     */
    public function getOne(?string $table = null)
    {
        if (empty($table) && empty($this->queryBuilder->getQueryPart('from'))) {
            throw new Exception('Unable to fetch data, table name required');
        }

        $this->limit(1);
        if (null !== $table) {
            $this->from($table);
        }
        $this->executeQueryInCompatMode();

        return $this->queryStatement->fetchAssociative();
    }

    /**
     * {@inheritdoc}
     */
    public function insert(string $table, ?array $columns): string
    {
        if (empty($table)) {
            throw new Exception('Unable to insert, table name is required.');
        }
        if (empty($columns) || !is_array($columns)) {
            throw new Exception('Unable to insert, at least one column is required.');
        }

        // Split table into name and alias
        list($tableName, $alias) = $this->decomposeFromExpression($table);
        // Set update query
        $this->queryBuilder->insert($tableName, $alias);
        // Set values
        foreach ($columns as $key => $value) {
            if (empty($key)) {
                continue;
            }

            $this->queryBuilder->setValue("`{$key}`", $parameterName = ":{$key}");
            $this->queryBuilder->setParameter($parameterName, $value);
        }

        $this->executeQueryInCompatMode();
        $this->initalRowsAmount = 1;

        return $this->connection->lastInsertId();
    }

    /**
     * {@inheritdoc}
     */
    public function insertMany(string $table, ?array $entries): int
    {
        if (empty($table)) {
            throw new Exception('Unable to insert, table name is required.');
        }
        if (empty($entries) || !is_array($entries)) {
            throw new Exception('Unable to insert, at least one column is required.');
        }
        if (empty($entries[0]) || !is_array($entries[0])) {
            throw new Exception('Unable to insert, at least one column is required in data arrays.');
        }

        $params = [];
        $columns = array_keys($entries[0]);
        $columnsAmount = count($columns);
        $placeholder = '(' . implode(', ', array_fill(0, $columnsAmount, '?')) . ')';
        $placeholders = [];
        foreach ($entries as $entry) {
            if (empty($entry)) {
                throw new Exception('The entry cannot be empty.');
            }
            if ($columnsAmount !== count($entry)) {
                throw new Exception('The entries must have the same number of arguments.');
            }

            $placeholders[] = $placeholder;
            $params = array_merge($params, array_values($entry));
        }
        $query = sprintf(
            "INSERT INTO `{$table}` (%s) VALUES %s",
            implode(', ', array_map(function (string $key) { return "`{$key}`"; }, $columns)),
            implode(', ', $placeholders)
        );

        $this->executeQueryInCompatMode($query, $params);
        $this->initalRowsAmount = count($entries);

        return $this->queryResult;
    }

    /**
     * {@inheritdoc}
     */
    public function update(string $table, ?array $columns): bool
    {
        if (empty($table)) {
            throw new Exception('Unable to update, table name is required.');
        }
        if (empty($columns) || !is_array($columns)) {
            throw new Exception('Unable to update, at least one column is required.');
        }

        // Split table into name and alias
        list($tableName, $alias) = $this->decomposeFromExpression($table);
        // Set update query
        $this->queryBuilder->update($tableName, $alias);

        //region Values
        // Set values
        $set = array_filter($columns, function ($key) { return !empty($key); }, ARRAY_FILTER_USE_KEY);
        $types = [];
        $parameters = [];
        $oldParameters = $this->queryBuilder->getParameters();
        $oldTypes = $this->queryBuilder->getParameterTypes();
        $this->queryBuilder->setParameters([]);

        // Set update values
        foreach ($set as $key => $value) {
            $this->queryBuilder->set($key, '?');
            $parameters[] = $value;
        }

        // Shake parameters
        if (!empty($oldParameters)) {
            $k = count($parameters);
            foreach ($oldParameters as $key => $value) {
                if (!\is_string($key) && \is_int($key)) {
                    $paramKey = $k;
                    ++$k;
                } else {
                    $paramKey = $key;
                }

                $parameters[$paramKey] = $value;
                if (isset($oldTypes[$key])) {
                    $types[$paramKey] = $oldTypes[$key];
                }
            }
        }

        $this->queryBuilder->setParameters($parameters, $types);
        //endregion Values

        $this->executeQueryInCompatMode();
        $this->initalRowsAmount = 1;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $table, ?string $alias = null): bool
    {
        if (empty($table)) {
            throw new Exception('Unable to delete, table name is required.');
        }

        $this->queryBuilder->delete($table, null === $alias || $table === $alias ? null : $alias);
        $this->executeQueryInCompatMode(
            $this->getSQLForDelete(),
            $this->queryBuilder->getParameters(),
            $this->queryBuilder->getParameterTypes(),
        );
        $this->reset();

        return true;
    }

    /**
     * Executes the query.
     *
     * @param array<int,null|int|string|Type>|array<string,null|int|string|Type> $types
     */
    public function executeQuery(
        string $sql,
        array $params = [],
        array $types = [],
        bool $isRaw = false
    ): void {
        // Check if type of query is SELECT
        $isSelect = QueryBuilder::SELECT === $this->queryBuilder->getType();
        if ($isRaw) {
            $isSelect = true === (bool) \preg_match_all('/^SELECT\\s/imu', \trim($sql));
        }

        try {
            if ($isSelect) {
                $result = $this->connection->executeQuery($sql, $params, $types);
            } else {
                $result = $this->connection->executeStatement($sql, $params, $types);
            }
        } catch (DBALException $exception) {
            // Wrap DBALException in simple exception to preserve backward compatibility
            throw new Exception(sprintf('PDO Error: %s Query: %s', $exception->getMessage(), $sql), 0, $exception);
        }

        // Store query result
        $this->queryStatement = ($result instanceof Statement || $result instanceof DriverStatement) ? $result : $this->connection->prepare($sql);
        $this->queryResult = $result;
        $this->lastQuery = $sql;
        if (!$isRaw) {
            $this->reset();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        $this->fromAlias = null;
        $this->fromTable = null;
        $this->queryBuilder = $this->connection->createQueryBuilder();
        $this->queryBuilder->select('*');
    }

    /**
     * Decompozes the FROM expressions into table name and alias.
     */
    private function decomposeFromExpression(string $from): array
    {
        $pattern = '/^((?P<from>((\([\s\S]+\))|(([0-9a-z$_]+)|(`[^`]+`)|([\x{0080}-\x{FFFF}]+))))(((\s+AS\s+)|(\s+))(?P<alias>(([0-9a-z$_]+)|(`[^`]+`)|([\x{0080}-\x{FFFF}]+))))?)((\s+)?,.+)?$/imu';
        if (!preg_match_all($pattern, $from, $matches, PREG_PATTERN_ORDER | PREG_UNMATCHED_AS_NULL, 0)) {
            return [trim($from, '`'), null];
        }

        $table = $matches['from'][0] ?? null;
        $alias = $matches['alias'][0] ?? null;

        return [
            null !== $table ? trim($table, '`') : null,
            null !== $alias ? trim($alias, '`') : null,
        ];
    }

    /**
     * Normalizes IN elements.
     *
     * @param mixed $elements
     */
    private function decomposeElementsList($elements): array
    {
        if (!is_array($elements)) {
            $elements = array_map('trim', explode(',', (string) $elements));
        }

        return [$elements, array_fill(0, count($elements), '?')];
    }

    /**
     * Adds parameters to the query.
     */
    private function addParameters(array $parameters, array $types = []): void
    {
        if (empty($parameters)) {
            return;
        }

        foreach ($parameters as $parameterName => $parameterValue) {
            if (is_string($parameterName)) {
                $this->queryBuilder->createNamedParameter($parameterValue, $types[$parameterName] ?? null, ":{$parameterName}");
            } else {
                $this->queryBuilder->createPositionalParameter($parameterValue, $types[$parameterName] ?? null);
            }
        }
    }

    /**
     * Returns the SQL for DELETE query with added support of the table alias.
     */
    private function getSQLForDelete(): string
    {
        list('table' => $table, 'alias' => $alias) = $this->queryBuilder->getQueryPart('from');
        if (null !== $alias) {
            $where = $this->queryBuilder->getQueryPart('where');

            return "DELETE {$alias} FROM {$table} {$alias}" . (null !== $where ? ' WHERE ' . ((string) $where) : '');
        }

        return $this->queryBuilder->getSQL();
    }

    /**
     * Executes query in compatibility mode.
     */
    private function executeQueryInCompatMode(?string $query = null, ?array $params = null, ?array $types = null): void
    {
        if (null !== $query) {
            $this->executeQuery(
                $query,
                $params ?? [],
                $types ?? [],
                true
            );

            return;
        }

        if (!empty($params)) {
            $this->addParameters($params, $types ?? []);
        }
        $this->executeQuery(
            $this->queryBuilder->getSQL(),
            $this->queryBuilder->getParameters(),
            (array) $this->queryBuilder->getParameterTypes(),
            false
        );
    }
}
