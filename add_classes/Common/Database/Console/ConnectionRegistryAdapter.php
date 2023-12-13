<?php

declare(strict_types=1);

namespace App\Common\Database\Console;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Tools\Console\ConnectionNotFound;
use Doctrine\DBAL\Tools\Console\ConnectionProvider;
use Doctrine\Persistence\ConnectionRegistry;

final class ConnectionRegistryAdapter implements ConnectionProvider
{
    /**
     * The connection registry.
     */
    private ConnectionRegistry $connectionRegistry;

    /**
     * @param ConnectionRegistry $connectionRegistry The connection registry
     */
    public function __construct(ConnectionRegistry $connectionRegistry)
    {
        $this->connectionRegistry = $connectionRegistry;
    }

    /**
     * Returns the default connection.
     */
    public function getDefaultConnection(): Connection
    {
        return $this->getConnection($this->connectionRegistry->getDefaultConnectionName());
    }

    /**
     * Returns the connection by name.
     *
     * @throws ConnectionNotFound in case a connection with the given name does not exist
     */
    public function getConnection(string $name): Connection
    {
        try {
            return $this->connectionRegistry->getConnection($name);
        } catch (\Throwable $e) {
            new ConnectionNotFound(\sprintf('Failed to find the connection due to error: %s', $e->getMessage()), 0, $e);
        }
    }
}
