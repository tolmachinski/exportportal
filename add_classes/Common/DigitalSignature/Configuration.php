<?php

declare(strict_types=1);

namespace App\Common\DigitalSignature;

use App\Common\DigitalSignature\Provider\ProviderInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('digital_signatures');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->fixXmlConfig('service')
            ->children()
                ->arrayNode('services')
                    ->info('The digital signature service')
                    ->isRequired()
                    ->normalizeKeys(true)
                    ->ignoreExtraKeys(false)
                    ->useAttributeAsKey('service')
                    ->beforeNormalization()
                        ->ifString()
                        ->then(function ($v) { return ['type' => $v]; })
                    ->end()
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('type')
                                ->info('The digital signature service class name.')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->cannotBeOverwritten(true)
                                ->validate()
                                    ->ifTrue(fn ($v) => !\is_a($v, ProviderInterface::class, true))
                                    ->thenInvalid(sprintf('The "type" must be class name of the instance of the %s.', ProviderInterface::class))
                                ->end()
                            ->end()
                            ->arrayNode('options')
                                ->info('The digital signature service options')
                                ->useAttributeAsKey('variable')
                                ->prototype('variable')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
