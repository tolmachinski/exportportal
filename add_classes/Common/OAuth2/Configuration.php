<?php

declare(strict_types=1);

namespace App\Common\OAuth2;

use App\Common\OAuth2\Client\ClientConfigurationSectionTrait;
use App\Common\OAuth2\Client\Provider\Configurator\ConfiguratorInterface;
use App\Common\OAuth2\Client\Provider\ProviderFactoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    use ClientConfigurationSectionTrait;

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('oauth2');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $this->addClientsSection($rootNode);

        return $treeBuilder;
    }
}
