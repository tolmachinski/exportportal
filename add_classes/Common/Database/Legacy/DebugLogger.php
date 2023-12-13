<?php

declare(strict_types=1);

namespace App\Common\Database\Legacy;

/**
 * Debug logger for legacy query builder.
 *
 * @deprecated
 */
final class DebugLogger implements SqlLoggerInterface
{
    /**
     * Executed SQL queries.
     *
     * @var array
     */
    private $queries = [];

    /**
     * Inidcates if logger is enabled.
     *
     * @var bool
     */
    private $enabled = false;

    /**
     * Creates the instance of debug logger.
     */
    public function __construct(bool $enabled = false)
    {
        $this->enabled = $enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function log(
        string $query,
        ?array $params,
        ?string $sqlType,
        ?int $mode,
        ?float $time,
        bool $prepared = false,
        bool $executed = false,
        ?\Throwable $exception = null
    ): void {
        if (!$this->enabled) {
            return;
        }

        $record = [
            'query'    => $query,
            'params'   => $params,
            'mode'     => $mode,
            'type'     => $sqlType,
            'prepared' => $prepared,
            'executed' => $executed,
            'duration' => microtime(true) - $time,
        ];

        if (null !== $exception) {
            $record['exception'] = $exception;
        }

        $this->queries[] = $record;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecords(): array
    {
        return $this->queries;
    }
}
