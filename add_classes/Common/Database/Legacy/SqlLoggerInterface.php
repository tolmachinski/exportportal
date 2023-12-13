<?php

declare(strict_types=1);

namespace App\Common\Database\Legacy;

use Throwable;

/**
 * SQL logger interface for legacy query builder.
 *
 * @deprecated
 */
interface SqlLoggerInterface
{
    /**
     * Logs the query.
     *
     * @param Throwable $exception
     */
    public function log(
        string $query,
        ?array $params,
        ?string $sqlType,
        ?int $mode,
        ?float $time,
        bool $prepared = false,
        bool $executed = false,
        Throwable $exception = null
    ): void;

    /**
     * Returns the list of records.
     */
    public function getRecords(): array;
}
