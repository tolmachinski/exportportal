<?php

declare(strict_types=1);

namespace App\Common\Database;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;

/**
 * The Doctrine Connection factory contract.
 */
interface ConnectionFactoryInterface
{
    /**
     * Create a connection by name.
     *
     * @param mixed[]               $params
     * @param array<string, string> $mappingTypes
     *
     * @return Connection
     */
    public function createConnection(array $params, ?Configuration $config = null, ?EventManager $eventManager = null, array $mappingTypes = []);
}
