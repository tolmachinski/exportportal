<?php

declare(strict_types=1);

namespace App\Common\DependencyInjection\ServiceLocator;

use Closure;
use Symfony\Component\DependencyInjection\ServiceLocator as BaseServiceLocator;

/**
 * @internal
 */
class AnonymousServiceLocator extends BaseServiceLocator
{
    /**
     * The service factory.
     */
    private Closure $factory;

    /**
     * The map of the defined services.
     */
    private array $serviceMap = [];

    /**
     * The map of types of the defined services.
     */
    private array $serviceTypes = [];

    public function __construct(Closure $factory)
    {
        $this->factory = $factory;
        parent::__construct([]);
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed
     */
    public function get($id)
    {
        if (isset($this->serviceMap[$id])) {
            return $this->serviceMap[$id];
        }
        $this->serviceTypes[$id] = \get_class(
            $this->serviceMap[$id] = ($this->factory)($id) ?? parent::get($id)
        );

        return $this->serviceMap[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function getProvidedServices(): array
    {
        return $this->serviceTypes ?? $this->serviceTypes = array_map(function () { return '?'; }, $this->serviceMap);
    }
}
