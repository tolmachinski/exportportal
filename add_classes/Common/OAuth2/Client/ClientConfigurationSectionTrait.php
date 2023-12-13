<?php

declare(strict_types=1);

namespace App\Common\OAuth2\Client;

use App\Common\OAuth2\Client\Token\Storage\StorageInterface;
use League\OAuth2\Client\Provider\AbstractProvider;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\BooleanNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

trait ClientConfigurationSectionTrait
{
    /**
     * Adds OAuth2 clients configuration setting.
     */
    private function addClientsSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->fixXmlConfig('client')
            ->children()
                ->arrayNode('clients')
                    ->normalizeKeys(true)
                    ->useAttributeAsKey('client')
                    ->arrayPrototype()
                        ->append($this->getStatelessNodeDefinition())
                        ->append($this->getGrandTypeNodeDefinition())
                        ->append($this->getClientScopesNodeDefinition())
                        ->append($this->getProviderNodeDefinition())
                        ->append($this->getStorageNodeDefinition())
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Gets the configuration of the "stateless" node.
     */
    private function getStatelessNodeDefinition(): NodeDefinition
    {
        $node = new BooleanNodeDefinition('stateless');
        $node
            ->info('The flag that indicates if client is stateless or not.')
            ->defaultFalse()
        ;

        return $node;
    }

    /**
     * Gets the configuration of the "grant_type" node.
     */
    private function getGrandTypeNodeDefinition(): NodeDefinition
    {
        $node = new ScalarNodeDefinition('grant_type');
        $node
            ->info('The flag that indicates the grant type used by client to request access token.')
            ->defaultFalse()
        ;

        return $node;
    }

    /**
     * Gets the configuration of the "client_scopes" node.
     */
    private function getClientScopesNodeDefinition(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('client_scopes');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->info('The list of default client scopes used for authorization if none is provided')
            ->scalarPrototype()
            ->end()
        ;

        return $rootNode;
    }

    /**
     * Gets the configuration of the "provider" node.
     */
    private function getProviderNodeDefinition(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('provider');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->info('The OAuth2 provider')
            ->beforeNormalization()
                ->ifString()
                ->then(function ($v) { return ['type' => $v]; })
            ->end()
            ->isRequired()
            ->ignoreExtraKeys(false)
            ->children()
                ->scalarNode('type')
                    ->info('The OAuth2 client provider class name.')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->cannotBeOverwritten(true)
                    ->validate()
                        ->ifTrue(fn ($v) => !\is_a($v, AbstractProvider::class, true))
                        ->thenInvalid(sprintf('The "type" must be class name of the instance of the %s.', AbstractProvider::class))
                    ->end()
                ->end()
                ->scalarNode('client_id')
                    ->info('The client ID assigned by the provider.')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->cannotBeOverwritten(true)
                ->end()
                ->scalarNode('client_secret')
                    ->info('The client password assigned by the provider.')
                    ->cannotBeOverwritten(false)
                ->end()
                ->scalarNode('redirect_uri')
                    ->info('The redirect URI used for authorization.')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->cannotBeOverwritten(false)
                    ->defaultValue('http://localhost')
                    ->validate()
                        ->ifTrue(fn ($v) => false === filter_var($v, \FILTER_VALIDATE_URL))
                        ->thenInvalid('The "redirect_uri" must be a valid URI address.')
                    ->end()
                ->end()
                ->scalarNode('response_type')
                    ->info('The value that informs the authorization server of the desired authorization processing flow.')
                    ->defaultValue('code')
                ->end()
            ->end()
        ;

        return $rootNode;
    }

    /**
     * Gets the configuration of the "storage" node.
     */
    private function getStorageNodeDefinition(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('storage');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->info('The token storage')
            ->beforeNormalization()
                ->ifString()
                ->then(function ($v) { return ['type' => $v]; })
            ->end()
            ->children()
                ->scalarNode('type')
                    ->info('The token storage type')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(fn ($v) => !\is_a($v, StorageInterface::class, true))
                        ->thenInvalid(sprintf('The type of the storage must be the class name that is instance of the %s.', StorageInterface::class))
                    ->end()
                ->end()
                ->arrayNode('options')
                    ->info('The token storage')
                    ->useAttributeAsKey('variable')
                    ->prototype('variable')
                    ->end()
                ->end()
            ->end()
        ;

        return $rootNode;
    }
}
