<?php

declare(strict_types=1);

namespace App\Common\Database\Concerns;

use Doctrine\DBAL\Query\QueryBuilder;
use InvalidArgumentException;

/**
 * Allows to prepare and perform batch operations.
 */
trait HasBatchOperations
{
    /**
     * Validates the batch insert records.
     *
     * @throws InvalidArgumentException if records are invalid
     */
    private function validateBatchInsertRecordSet(array $records): void
    {
        if (empty($records)) {
            throw new InvalidArgumentException('Unable to insert, at least one column is required.');
        }

        $firstRecord = \current($records);
        $columnsNames = \is_array($firstRecord) ? \array_keys($firstRecord) : [];
        $columnsAmount = \count($columnsNames);
        \sort($columnsNames);
        foreach ($records as $record) {
            if (empty($record)) {
                throw new InvalidArgumentException('The batch insert entry cannot be empty.');
            }
            if (!\is_array($record)) {
                throw new InvalidArgumentException('The batch insert must be of array type');
            }
            \ksort($record);
            if (\count($record) !== $columnsAmount || \array_keys($record) !== $columnsNames) {
                throw new InvalidArgumentException('The batch insert records must have the same columns.');
            }
        }
    }

    /**
     * Creates the SQL query for batch INSERT.
     */
    private function createBatchInsertSqlQuery(QueryBuilder $queryBuilder, array $records, string $table): string
    {
        $rawColumns = \array_keys($records[0]);
        $columnNames = \array_map(fn (string $column) => "`{$column}`", $rawColumns);
        $placeholders = [];
        foreach (\array_values($records) as $index => $record) {
            $keys = [];
            foreach ($record as $column => $value) {
                $keys[] = $queryBuilder->createNamedParameter($value, null, ":{$column}{$index}");
            }

            $placeholders[] = '(' . \implode(', ', $keys) . ')';
        }

        return \sprintf(
            "INSERT INTO `{$table}` (%s) VALUES %s",
            \implode(', ', $columnNames),
            \implode(', ', $placeholders)
        );
    }
}
