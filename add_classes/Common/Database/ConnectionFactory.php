<?php

declare(strict_types=1);

namespace App\Common\Database;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;

/**
 * The Doctrine Connection factory.
 */
final class ConnectionFactory implements ConnectionFactoryInterface
{
    /**
     * The map of Dcotrine types (additional or overriden).
     *
     * @var mixed[]
     */
    private array $typesConfig = [];

    /**
     * The flag that indicates if types were intialized.
     *
     * @var bool
     */
    private $initialized = false;

    /**
     * The list of database driver aliases.
     *
     * @var array<string,string>
     */
    private $driverAliases = [
        'oci'        => 'pdo_oci',
        'mssql'      => 'pdo_sqlsrv',
        'mysql'      => 'pdo_mysql',
        'mysql2'     => 'pdo_mysql',
        'postgres'   => 'pdo_pgsql',
        'postgresql' => 'pdo_pgsql',
        'pgsql'      => 'pdo_pgsql',
        'sqlite'     => 'pdo_sqlite',
        'sqlite3'    => 'pdo_sqlite',
    ];

    /**
     * @param array $typesConfig The map of Dcotrine types (additional or overriden)
     */
    public function __construct(array $typesConfig = [])
    {
        $this->typesConfig = $typesConfig;
    }

    /**
     * Create a connection by name.
     *
     * @param mixed[]               $params
     * @param array<string, string> $mappingTypes
     *
     * @return Connection
     */
    public function createConnection(array $params, ?Configuration $config = null, ?EventManager $eventManager = null, array $mappingTypes = [])
    {
        if (!$this->initialized) {
            $this->initializeTypes();
        }

        if (empty($params)) {
            throw new InvalidArgumentException('Connection configurations are missing.');
        }
        $params = $this->normalizeConfigurations($params);
        $overriddenOptions = $params['connection_override_options'] ?? [];
        unset($params['connection_override_options']);

        if (!isset($params['pdo']) && (!isset($params['charset']) || $overriddenOptions)) {
            $wrapperClass = null;
            if (isset($params['wrapperClass'])) {
                if (!is_subclass_of($params['wrapperClass'], Connection::class)) {
                    throw new Exception(
                        \sprintf('The given \'wrapperClass\' %s has to be a subtype of %s', $params['wrapperClass'], Connection::class)
                    );
                }

                $wrapperClass = $params['wrapperClass'];
                $params['wrapperClass'] = null;
            }

            $connection = DriverManager::getConnection($params, $config, $eventManager);
            $params = array_merge($connection->getParams(), $overriddenOptions);
            $driver = $connection->getDriver();

            if ($driver instanceof AbstractMySQLDriver) {
                $params['charset'] = 'utf8mb4';
                if (!isset($params['defaultTableOptions']['collate'])) {
                    $params['defaultTableOptions']['collate'] = 'utf8mb4_0900_ai_ci';
                }
            } else {
                $params['charset'] = 'utf8';
            }

            if (null !== $wrapperClass) {
                $params['wrapperClass'] = $wrapperClass;
            } else {
                $wrapperClass = Connection::class;
            }

            $connection = new $wrapperClass($params, $driver, $config, $eventManager);
        } else {
            $connection = DriverManager::getConnection($params, $config, $eventManager);
        }

        $platform = $this->getDatabasePlatform($connection);
        if (!empty($mappingTypes)) {
            foreach ($mappingTypes as $dbType => $doctrineType) {
                $platform->registerDoctrineTypeMapping($dbType, $doctrineType);
            }
        }
        $platformMappingTypes = CustomTypesProvider::getPlatformTypes($platform);
        if (!empty($platformMappingTypes)) {
            $this->registerTypes($platformMappingTypes);
            foreach (array_keys($platformMappingTypes) as $dbType) {
                $platform->registerDoctrineTypeMapping($dbType, $dbType);
            }
        }

        return $connection;
    }

    /**
     * Normalizes the configurations to prevent BC breaks and other nasty stuff.
     */
    private function normalizeConfigurations(array $configs): array
    {
        $configs = array_merge($configs, [
            'url'           => $configs['url'] ?? (!empty($configs['dsn']) ? $configs['dsn'] : null),
            'driver'        => $this->detectDriverName($configs),
            'password'      => $configs['password'] ?? $configs['pass'] ?? null,
            'dbname'        => $configs['dbname'] ?? $configs['name'] ?? null,
            'driverOptions' => array_merge([\PDO::MYSQL_ATTR_LOCAL_INFILE => true], $configs['driverOptions'] ?? []),
        ]);

        unset($configs['type'], $configs['pass'], $configs['name'], $configs['plugin'], $configs['pdo_attr_local_infile']);

        return $configs;
    }

    /**
     * Detects the driver name.
     */
    private function detectDriverName(array $configs): ?string
    {
        $driverName = $configs['driver'] ?? $configs['type'] ?? null;
        if (null !== $driverName) {
            if (isset($this->driverAliases[$driverName])) {
                $driverName = $this->driverAliases[$driverName];
            }
        }

        return $driverName;
    }

    /**
     * Try to get the database platform.
     *
     * This could fail if types should be registered to an predefined/unused connection and the platform version is unknown.
     *
     * @throws Exception
     */
    private function getDatabasePlatform(Connection $connection): AbstractPlatform
    {
        try {
            return $connection->getDatabasePlatform();
        } catch (DriverException $driverException) {
            throw new Exception('An exception occurred while establishing a connection to figure out your platform version.', 0, $driverException);
        }
    }

    /**
     * Initialize the types.
     */
    private function initializeTypes(): void
    {
        $this->registerTypes($this->typesConfig);
        $this->initialized = true;
    }

    /**
     * Registers the types.
     */
    private function registerTypes(array $types): void
    {
        foreach ($types as $typeName => $typeClass) {
            if (Type::hasType($typeName)) {
                Type::overrideType($typeName, $typeClass);
            } else {
                Type::addType($typeName, $typeClass);
            }
        }
    }
}
