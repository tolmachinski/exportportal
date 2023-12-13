<?php

declare(strict_types=1);

namespace App\Common\DependencyInjection\ServiceLocator;

use Closure;
use Doctrine\Persistence\ConnectionRegistry;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Contracts\Service\ResetInterface;

final class ModelLocator extends ServiceLocator implements ResetInterface
{
    /**
     * The service factory.
     */
    private Closure $factory;

    /**
     * The connection registry.
     */
    private ConnectionRegistry $connectionRegistry;

    /**
     * The map of the defined services.
     */
    private array $serviceMap = [];

    /**
     * The map of types of the defined services.
     */
    private array $serviceTypes = [];

    public function __construct(ConnectionRegistry $connectionRegistry)
    {
        parent::__construct([]);

        $this->connectionRegistry = $connectionRegistry;
        $this->factory = function (string $name) {
            $model = null;
            $connection = null;
            $modelAlias = null;
            $modelName = $name;
            if (\str_contains($name, '@') || \str_contains($name, ':')) {
                if (\preg_match('/^([^:@]++)(:([^@]*))?(@(.*))?$/i', $name, $matches)) {
                    list(1 => $modelName, 3 => $modelAlias, 5 => $connection) = $matches;
                }
            }
            $modelAlias = $modelAlias ?: $modelName;
            $connection = $connection ?: $this->connectionRegistry->getDefaultConnectionName();
            $possibleNames = \array_unique([$name, $modelName, \strtolower($modelName), $modelAlias, "{$modelName}:{$modelAlias}@{$connection}"]);
            foreach ($possibleNames as $name) {
                if (isset($this->serviceMap[$name]) && ($this->serviceMap[$name] instanceof $modelName)) {
                    $model = $this->serviceMap[$name];

                    break;
                }
            }
            $model = $model ?? new $modelName($this->connectionRegistry->getConnection($connection));
            $modelClass = \get_class($model);
            foreach ($possibleNames as $name) {
                if (isset($this->serviceMap[$name])) {
                    continue;
                }

                $this->serviceMap[$name] = $model;
                $this->serviceTypes[$name] = $modelClass;
            }

            return $model;
        };
    }

    /**
     * Reset an object to its initial state.
     */
    public function reset()
    {
        $this->serviceMap = [];
        $this->serviceTypes = [];
    }

    /**
     * {@inheritdoc}
     *
     * @return object
     */
    public function get($id)
    {
        return $this->serviceMap[$id] ?? ($this->factory)($id) ?? parent::get($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getProvidedServices(): array
    {
        return $this->serviceTypes ?? $this->serviceTypes = array_map(function () { return '?'; }, $this->serviceMap);
    }
}
