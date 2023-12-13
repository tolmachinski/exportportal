<?php

declare(strict_types=1);

namespace App\Common\Database\Legacy;

use InvalidArgumentException;
use LogicException;
use PDO;

/**
 * The adapter for Doctrine DBAL.
 *
 * @deprecated
 */
final class PdoConnectionFactory implements ConnectionFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createConnection(array $configs): PDO
    {
        if (!class_exists('PDO', false)) {
            throw new LogicException('The PDO extension is required.');
        }
        if (empty($configs)) {
            throw new InvalidArgumentException('Connection configurations are missing.');
        }

        $configs = $this->normalizeConfigurations($configs);
        $pdo = new PDO($configs['url'], $configs['user'], $configs['password'], $configs['driverOptions']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("SET CHARACTER SET {$configs['charset']};");

        return $pdo;
    }

    /**
     * Normalizes the configurations to prevent BC breaks and other nasty stuff.
     */
    private function normalizeConfigurations(array $configs): array
    {
        $configs = array_merge($configs, [
            'url'              => $this->resolveConnectionUrl($configs),
            'password'         => $configs['password'] ?? $configs['pass'] ?? null,
            'dbname'           => $configs['dbname'] ?? $configs['name'] ?? null,
            'charset'          => $configs['charset'] ?? 'utf8',
            'collation'        => $configs['collation'] ?? 'utf8_unicode_ci',
            'driverOptions'    => $configs['driverOptions'] ?? [],
        ]);
        $configs['driverOptions'][\PDO::ATTR_PERSISTENT] = !empty($configs['persistent']) ? true : false;
        if (isset($configs['pdo_attr_local_infile']) && $configs['pdo_attr_local_infile']) {
            $config['driverOptions'][\PDO::MYSQL_ATTR_LOCAL_INFILE] = true;
        }

        unset($configs['type'], $configs['pass'], $configs['name'], $configs['plugin'], $configs['pdo_attr_local_infile']);

        return $configs;
    }

    /**
     * Returns the DSN URL.
     */
    private function resolveConnectionUrl(array $configs): string
    {
        $dsn = $configs['url'] ?? $configs['dsn'] ?? null;
        if (null === $dsn) {
            $dsn = "{$configs['type']}:host={$configs['host']};dbname={$configs['name']};charset={$configs['charset']}";
        }

        return $dsn;
    }
}
