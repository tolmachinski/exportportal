<?php

declare(strict_types=1);

namespace App\DigitalSignature\Provider\DocuSign;

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
        $treeBuilder = new TreeBuilder('options');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->fixXmlConfig('template')
            ->children()
                ->scalarNode('base_path')
                    ->info('The URL of the DocuSign API.')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(fn ($v) => !\filter_var($v, \FILTER_VALIDATE_URL))
                        ->thenInvalid('The "base_path" must be a valid URL.')
                    ->end()
                ->end()
                ->scalarNode('account_id')
                    ->info('The default DocuSign account ID.')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('auth_token')
                    ->info('The default DocuSign authorization token.')
                ->end()
                ->scalarNode('auth_client')
                    ->info('The authorization client name.')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('templates')
                    ->info('The list of templates used in DocuSign processes.')
                    ->isRequired()
                    ->normalizeKeys(true)
                    ->ignoreExtraKeys(false)
                    ->children()
                        ->scalarNode('email_subject')
                            ->info('The email subject template.')
                        ->end()
                        ->scalarNode('email_message')
                            ->info('The email mesasge template.')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
