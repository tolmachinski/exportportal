<?php

declare(strict_types=1);

namespace App\Common\Database;

use TinyMVC_PDO as ConnectionHandler;
use function Symfony\Component\String\s;

/**
 * Abstract model that is used to bind model to single table.
 */
abstract class Model extends BaseModel
{
    use Concerns\GuardsAttributes;
    use Concerns\ClearsNullables;
    use Concerns\AppendsTimestamps;
    use Concerns\CastsAttributes;
    use Concerns\ConvertsAttributes;
    use Concerns\HasBatchOperations;

    /**
     * The name of the "created at" column.
     *
     * @var null|string
     */
    protected const CREATED_AT = null;

    /**
     * The name of the "updated at" column.
     *
     * @var null|string
     */
    protected const UPDATED_AT = null;

    /**
     * The table name.
     */
    protected string $table;

    /**
     * The table alias.
     *
     * @deprecated aliasing of the models is deprecated
     */
    protected string $alias;

    /**
     * The table primary key/keys.
     * It is recommended to avoid usage of composite primary keys.
     *
     * @var array|string
     */
    protected $primaryKey = 'id';

    /**
     * The amount of records per page.
     */
    protected int $perPage;

    /**
     * {@inheritdoc}
     */
    public function __construct(ConnectionHandler $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->boot();
    }

    /**
     * Returns the table name.
     */
    final public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Returns the table alias (if exists).
     *
     * @deprecated aliasing of the models is deprecated
     */
    final public function getAlias(): ?string
    {
        return $this->alias;
    }

    /**
     * Returns the primary key/keys.
     *
     * @return array|string
     */
    final public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * Get the default foreign key name for the model.
     */
    final public function getForeignKey(): string
    {
        $referenceName = s(classBasename($this))->snake();
        if ($referenceName->endsWith('_model')) {
            $referenceName = $referenceName->slice(0, -6);
        }

        return (string) $referenceName->append('_' . (\is_string($this->getPrimaryKey()) ? $this->getPrimaryKey() : 'id'));
    }

    /**
     * Returns the amount of records per page.
     */
    final public function getPerPage(): int
    {
        return $this->perPage ?? 0;
    }

    /**
     * Qualify the given column name by the model's table.
     */
    public function qualifyColumn(string $column): string
    {
        if (false !== mb_strpos($column, '.')) {
            return $column;
        }

        return "{$this->getTable()}.{$column}";
    }

    /**
     * Checks if record with such ID exists.
     *
     * @param mixed $id
     */
    public function has($id): bool
    {
        $counter = $this->findRecord(
            null,
            $this->getTable(),
            null,
            $this->getPrimaryKey(),
            $id,
            ['columns' => ['COUNT(*) AS `AGGREGATE`']]
        );

        return (bool) (int) ($counter['AGGREGATE'] ?? 0);
    }

    /**
     * Finds a single record by its primary key/ identifier.
     *
     * @param mixed $id
     *
     * @deprecated
     * @see self::findOne()
     *
     * @uses self::findOne()
     */
    public function find($id): ?array
    {
        return $this->findOne($id);
    }

    /**
     * Finds a single record by its primary key identifier.
     *
     * @param mixed $id
     */
    public function findOne($id, array $params = []): ?array
    {
        return $this->castAttributesToNative(
            $this->findRecord(
                null,
                $this->getTable(),
                null,
                $this->getPrimaryKey(),
                $id,
                $params
            )
        );
    }

    /**
     * Finds all records in repository.
     */
    public function findAll(array $params = []): array
    {
        unset($params['conditions'], $params['scopes']);

        return $this->restoreAttributesList(
            $this->findRecords(
                null,
                $this->getTable(),
                null,
                $params
            )
        );
    }

    /**
     * Finds a single record by a set of criteria.
     */
    public function findOneBy(array $params = []): ?array
    {
        return $this->castAttributesToNative(
            $this->findRecord(
                null,
                $this->getTable(),
                null,
                null,
                null,
                $params
            )
        );
    }

    /**
     * Finds records by criteria.
     */
    public function findAllBy(array $params = []): array
    {
        return $this->restoreAttributesList(
            $this->findRecords(
                null,
                $this->getTable(),
                null,
                $params
            )
        );
    }

    /**
     * Paginate the records by criteria.
     */
    public function paginate(array $params = [], ?int $perPage = null, ?int $page = 1): array
    {
        $total = $this->countBy(array_replace($params, ['columns' => null, 'group' => null, 'limit' => null, 'skip' => null, 'order' => null]));
        $perPage = $perPage ?? $this->getPerPage();
        $lastPage = \max((int) \ceil($total / $perPage), 1);
        $currentPage = $page ?? 1;
        $hasMorePages = $currentPage < $lastPage;
        $hasPages = 1 !== $currentPage || $hasMorePages;
        $records = $total > 0 ? $this->findAllBy(array_replace($params, ['limit' => $perPage, 'skip'  => ($currentPage - 1) * $perPage])) : [];
        $from = \count($records) > 0 ? ($page - 1) * $perPage + 1 : null;
        $to = \count($records) > 0 ? $from + count($records) - 1 : null;

        return [
            'data'           => $records,
            'total'          => $total,
            'per_page'       => $perPage,
            'current_page'   => $page,
            'has_more_pages' => $hasMorePages,
            'last_page'      => $lastPage,
            'has_pages'      => $hasPages,
            'from'           => $from,
            'to'             => $to,
        ];
    }

    /**
     * Returns the paginator information.
     */
    public function getPaginator(array $params = [], ?int $perPage = null, ?int $page = 1): array
    {
        $total = $this->countBy(array_replace($params, ['columns' => null, 'group' => null, 'limit' => null, 'skip' => null, 'order' => null]));
        $perPage = $perPage ?? $this->getPerPage();
        $lastPage = \max((int) \ceil($total / $perPage), 1);
        $currentPage = $page ?? 1;
        $hasMorePages = $currentPage < $lastPage;
        $hasPages = 1 !== $currentPage || $hasMorePages;

        return [
            'total'          => $total,
            'per_page'       => $perPage,
            'current_page'   => $page,
            'has_more_pages' => $hasMorePages,
            'last_page'      => $lastPage,
            'has_pages'      => $hasPages,
        ];
    }

    /**
     * Counts the amount of records by criteria.
     */
    public function countAll(): int
    {
        $counter = $this->findRecord(
            null,
            $this->getTable(),
            null,
            null,
            null,
            ['columns' => ['COUNT(*) AS `AGGREGATE`']]
        );

        return (int) ($counter['AGGREGATE'] ?? 0);
    }

    /**
     * Counts the amount of records by criteria.
     *
     * @deprecated v2.29.6 in favor fo self::countAllBy()
     * @see self::countAllBy()
     *
     * @return array|int
     */
    public function countBy(array $params = [])
    {
        unset($params['with'], $params['with_count']);

        $params['columns'] = array_merge(['COUNT(*) AS `AGGREGATE`'], $params['columns'] ?? []);
        $counters = $this->findRecords(
            null,
            $this->getTable(),
            null,
            $params
        );

        if (!empty($params['group'])) {
            return $counters;
        }

        return (int) ($counters[0]['AGGREGATE'] ?? 0);
    }

    /**
     * Counts the amount of records by criteria.
     *
     * @return array|int
     */
    public function countAllBy(array $params = [])
    {
        unset($params['with'], $params['with_count']);
        if (!empty($params['group'])) {
            $params['columns'] = array_merge(['COUNT(*) AS `AGGREGATE`'], $params['columns'] ?? []);

            return $this->findRecords(
                null,
                $this->getTable(),
                null,
                $params
            );
        }
        $counters = $this->findRecords(null, $this->getTable(), null, \array_replace(
            $params,
            ['columns' => ['COUNT(*) AS `AGGREGATE`'], 'group' => null, 'limit' => null, 'skip' => null, 'order' => null]
        ));

        return (int) ($counters[0]['AGGREGATE'] ?? 0);
    }

    /**
     * Inserts one record.
     */
    public function insertOne(array $record): string
    {
        $builder = $this->createQueryBuilder()
            ->insert($this->getTable())
        ;
        foreach ($this->preFillAttributesProcessing($record) as $column => $value) {
            $builder->setValue($column, $builder->createNamedParameter($value, null, ":{$column}"));
        }
        $builder->execute();

        return $this->getConnection()->lastInsertId();
    }

    /**
     * Insert many records.
     */
    public function insertMany(array $records): int
    {
        $this->validateBatchInsertRecordSet($records);

        return $this->getConnection()->executeStatement(
            $this->createBatchInsertSqlQuery(
                $builder = $this->createQueryBuilder(),
                array_map(fn ($r) => $this->preFillAttributesProcessing($r), array_values($records)),
                $this->getTable()
            ),
            $builder->getParameters()
        );
    }

    /**
     * Updates one record.
     *
     * @param mixed $id
     */
    public function updateOne($id, array $record): bool
    {
        self::updateMany($record, [
            'scopes' => [
                'primary_key' => fn () => [$this->getTable(), $this->getPrimaryKey(), $id],
            ],
        ]);

        return true;
    }

    /**
     * Updates many records.
     */
    public function updateMany(array $record, array $params = []): int
    {
        $attributes = $this->preFillAttributesProcessing($record);
        // Remove date of creation from updated attributes to prevent situations when
        // the date is overriden by timestamsp update, but not in the cases
        // when it is running not in unguarded mode.
        if ($this->usesTimestamps() && null !== ($createdAt = $this->getCreatedAtColumn()) && !$this->isUnguarded()) {
            unset($attributes[$createdAt]);
        }

        return $this->updateRecords(
            null,
            $this->getTable(),
            null,
            $attributes,
            $params
        );
    }

    /**
     * Deletes one record.
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function deleteOne($id)
    {
        $this->scopePrimaryKey(
            $builder = $this->createQueryBuilder()->delete($this->getTable()),
            $this->getTable(),
            $this->getPrimaryKey(),
            $id
        );
        $builder->execute();

        return true;
    }

    /**
     * Deletes many records by criteria.
     */
    public function deleteAllBy(array $params = []): bool
    {
        return $this->removeRecords(
            null,
            $this->getTable(),
            null,
            $params
        );
    }

    /**
     * Boots the model.
     */
    private function boot(): void
    {
        $this->bootGuardConcern();
        $this->bootNullableConcern();
        $this->bootTimestampsConcern();
        $this->bootCastsConcern();
    }
}
