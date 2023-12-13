<?php

declare(strict_types=1);

namespace App\Common\Exceptions;

use App\Common\Database\Exceptions\QueryException as OriginalQueryException;
use TinyMVC_PDO as Connection;

/**
 * @deprecated
 */
class QueryException extends OriginalQueryException
{
    /**
     * The log record of the query that caused exception.
     */
    private array $logRecord;

    /**
     * The content of the query that cused the exption.
     */
    private ?string $queryString;

    /**
     * The parameters of the failed query.
     */
    private array $queryParameters;

    /**
     * {@inheritdoc}
     */
    public static function executionFailed(Connection $pdo, ?\Throwable $exception = null, int $code = 0): self
    {
        /** @var self $exception */
        $exception = parent::executionFailed($pdo, $exception, $code);
        $exception->setLogRecord([]);
        $exception->setParameters([]);
        $exception->setQuery(null);

        return $exception;
    }

    /**
     * Returns the query log record.
     *
     * @deprecated
     */
    public function getLogRecord(): ?array
    {
        return $this->logRecord;
    }

    /**
     * sets the query log record.
     *
     * @deprecated
     */
    public function setLogRecord(?array $logRecord): void
    {
        $this->logRecord = $logRecord;
    }

    /**
     * Returns the query content.
     *
     * @deprecated
     */
    public function getQuery(): ?string
    {
        return $this->queryString;
    }

    /**
     * Stes the query content.
     *
     * @deprecated
     */
    public function setQuery(?string $query): void
    {
        $this->queryString = $query;
    }

    /**
     * Returns the query parameters.
     *
     * @deprecated
     */
    public function getParamters(): ?array
    {
        return $this->queryParameters;
    }

    /**
     * Sets the query parameters.
     *
     * @deprecated
     */
    public function setParameters(?array $parameters): void
    {
        $this->queryParameters = $parameters;
    }
}
