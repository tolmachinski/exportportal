<?php

declare(strict_types=1);

namespace App\Common\OAuth2\Client\Provider\Configurator;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

interface ConfiguratorInterface
{
    /**
     * Indicates if provider is supported by configurator.
     */
    public function supports(string $name): bool;

    /**
     * Returns the provider options from configuratin.
     */
    public function getProviderOptions(array $config): array;

    /**
     * Adds the configuration section for the provided configuration tree.
     *
     * @param ArrayNodeDefinition|NodeDefinition $node
     */
    public function addConfigurationSection(NodeDefinition $node): void;
}
