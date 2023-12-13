<?php

declare(strict_types=1);

namespace App\Common\DependencyInjection\ServiceLocator;

use Closure;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Contracts\Service\ResetInterface;

final class LibraryLocator extends ServiceLocator implements ResetInterface
{
    /**
     * The service factory.
     */
    private Closure $factory;

    /**
     * The service container.
     */
    private ContainerInterface $serviceContainer;

    /**
     * The map of the defined services.
     */
    private array $serviceMap = [];

    /**
     * The map of types of the defined services.
     */
    private array $serviceTypes = [];

    public function __construct(ContainerInterface $serviceContainer)
    {
        parent::__construct([]);

        $this->serviceContainer = $serviceContainer;
        $this->factory = function (string $name) {
            $library = null;
            $libraryAlias = null;
            $libraryName = $name;
            if (\str_contains($name, ':')) {
                if (\preg_match('/^([^:@]++)(:(.*))?$/i', $name, $matches)) {
                    list(1 => $libraryName, 3 => $libraryAlias) = $matches;
                }
            }
            $libraryAlias = $libraryAlias ?: $libraryName;
            $possibleNames = \array_unique([$name, $libraryName, \strtolower($libraryName), $libraryAlias, "{$libraryName}:{$libraryAlias}"]);
            foreach ($possibleNames as $name) {
                if (isset($this->serviceMap[$name]) && ($this->serviceMap[$name] instanceof $libraryName)) {
                    $library = $this->serviceMap[$name];

                    break;
                }
            }
            $library = $library ?? new $libraryName($this->serviceContainer);
            $libraryClass = \get_class($library);
            foreach ($possibleNames as $name) {
                if (isset($this->serviceMap[$name])) {
                    continue;
                }

                $this->serviceMap[$name] = $library;
                $this->serviceTypes[$name] = $libraryClass;
            }

            return $library;
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
