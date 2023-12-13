<?php

declare(strict_types=1);

namespace App\Common\Database\Logger;

use Doctrine\DBAL\Logging\SQLLogger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

final class DbalLogger implements SQLLogger
{
    public const MAX_STRING_LENGTH = 32;

    public const BINARY_DATA_VALUE = '(binary value)';

    /**
     * The internal logger.
     */
    private LoggerInterface $logger;

    /**
     * The stopwatch.
     */
    private Stopwatch $stopwatch;

    /**
     * @param LoggerInterface $logger    the internal logger
     * @param Stopwatch       $stopwatch the stopwatch
     */
    public function __construct(LoggerInterface $logger = null, Stopwatch $stopwatch = null)
    {
        $this->logger = $logger;
        $this->stopwatch = $stopwatch;
    }

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null): void
    {
        if (null !== $this->stopwatch) {
            $this->stopwatch->start('doctrine', 'doctrine');
        }

        if (null !== $this->logger) {
            $this->logger->debug($sql, null === $params ? [] : $this->normalizeParams($params));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery(): void
    {
        if (null !== $this->stopwatch) {
            $this->stopwatch->stop('doctrine');
        }
    }

    /**
     * Normalize query parameters.
     */
    private function normalizeParams(array $params): array
    {
        foreach ($params as $index => $param) {
            // Normalize parameters recursively
            if (\is_array($param)) {
                $params[$index] = $this->normalizeParams($param);

                continue;
            }

            // If is string, then let it be
            if (!\is_string($params[$index])) {
                continue;
            }

            // Non utf-8 strings break json encoding
            if (!preg_match('//u', $params[$index])) {
                $params[$index] = self::BINARY_DATA_VALUE;

                continue;
            }

            // Detect if the too long string must be shorten
            if (self::MAX_STRING_LENGTH < mb_strlen($params[$index], 'UTF-8')) {
                $params[$index] = mb_substr($params[$index], 0, self::MAX_STRING_LENGTH - 6, 'UTF-8') . ' [...]';

                continue;
            }
        }

        return $params;
    }
}
