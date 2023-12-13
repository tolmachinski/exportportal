<?php

declare(strict_types=1);

namespace App\Common\Database\Connection;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ConnectionRegistry;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

class Registry implements ConnectionRegistry
{
    /**
     * The list of knwonw connections.
     */
    protected array $knownConnections;

    /**
     * The name of the default connection.
     */
    protected string $defaultConnection;

    /**
     * The container that contains the connections.
     */
    protected ContainerInterface $container;

    /**
     * @param string $defaultConnection the name of the default connection
     */
    public function __construct(ContainerInterface $container, array $connections, string $defaultConnection = 'default')
    {
        $this->container = $container;
        $this->knownConnections = $connections;
        $this->defaultConnection = $defaultConnection;
    }

    /**
     * Gets the named connection.
     *
     * @param string $name the connection name (null for the default one)
     *
     * @return object
     */
    public function getConnection($name = null)
    {
        $name = $name ?? $this->defaultConnection;
        if (!isset($this->knownConnections[$name])) {
            throw new InvalidArgumentException(
                \sprintf(
                    'The Doctrine connection with name "%s" does not exist. ' .
                    'You must indicate one of the following: %s',
                    $name,
                    \implode(', ', $this->getConnectionNames())
                )
            );
        }

        return $this->getService($this->knownConnections[$name]);
    }

    /**
     * Gets the default connection name.
     *
     * @return string the default connection name
     */
    public function getDefaultConnectionName()
    {
        return $this->defaultConnection;
    }

    /**
     * Gets all connection names.
     *
     * @return string[] an array of connection names
     */
    public function getConnectionNames()
    {
        return $this->knownConnections;
    }

    /**
     * Gets an array of all registered connections.
     *
     * @return Connection[] an array of Connection instances
     */
    public function getConnections()
    {
        $connections = [];
        foreach ($this->connections as $name => $id) {
            $connections[$name] = $this->getService($id);
        }

        return $connections;
    }

    /**
     * Fetches/creates the given services.
     */
    protected function getService(string $name)
    {
        return $this->container->get($name);
    }
}
