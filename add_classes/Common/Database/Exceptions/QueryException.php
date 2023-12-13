<?php

declare(strict_types=1);

namespace App\Common\Database\Exceptions;

use Doctrine\DBAL\Logging\SQLLogger;
use TinyMVC_PDO as Connection;

class QueryException extends DBException
{
    /**
     * The logger that collects the queries.
     */
    private ?SQLLogger $logger;

    public function __construct(Connection $pdo, ?\Throwable $previous = null, ?string $message = 'The execution of the query failed', int $code = 0)
    {
        parent::__construct($message ?? 'The execution of the query failed', $code, $previous);

        $logger = $pdo->getConnection()->getConfiguration()->getSQLLogger();
        if (null !== $logger) {
            $this->setLogger($logger);
        }
    }

    /**
     * Returns prepared exception.
     *
     * @deprecated The exception must be created using 'new'
     */
    public static function executionFailed(Connection $pdo, ?\Throwable $exception = null, int $code = 0): self
    {
        return new static($pdo, $exception, null, $code);
    }

    /**
     * Get the logger that collects the queries.
     */
    public function getLogger(): ?SQLLogger
    {
        return $this->logger;
    }

    /**
     * Set the logger that collects the queries.
     */
    public function setLogger(SQLLogger $logger): self
    {
        $this->logger = $logger;

        return $this;
    }
}
