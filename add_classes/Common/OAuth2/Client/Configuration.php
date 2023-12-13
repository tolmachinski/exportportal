<?php

declare(strict_types=1);

namespace App\Common\OAuth2\Client;

use App\Common\OAuth2\Client\Provider\Configurator\ClientSecretAwareConfiguratorInterface;
use App\Common\OAuth2\Client\Provider\Configurator\ConfiguratorInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    use ClientConfigurationSectionTrait;

    /**
     * The name of the client configuration.
     */
    private string $name;

    /**
     * The provider configurator.
     */
    private ConfiguratorInterface $configurator;

    public function __construct(string $name, ConfiguratorInterface $configurator)
    {
        $this->name = $name;
        $this->configurator = $configurator;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder($this->name);
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
            ->append($this->getStatelessNodeDefinition())
            ->append($this->getGrandTypeNodeDefinition())
            ->append($this->getClientScopesNodeDefinition())
            ->append($this->getProviderNodeDefinition())
            ->append($this->getStorageNodeDefinition())
            ->end()
        ;

        $configurator = $this->configurator;
        if ($configurator instanceof ClientSecretAwareConfiguratorInterface && $configurator->needsClientSecret()) {
            $rootNode
                ->find('provider.client_secret')
                    ->isRequired()
                    ->cannotBeEmpty();
        }
        $this->configurator->addConfigurationSection($rootNode->find('provider'));

        return $treeBuilder;
    }
}
