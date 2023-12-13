<?php

declare(strict_types=1);

namespace App\OAuth2\Client\Provider\Configurator;

use App\Common\OAuth2\Client\Provider\Configurator\ClientSecretAwareConfiguratorInterface;
use App\Common\OAuth2\Client\Provider\Configurator\ConfiguratorInterface;
use App\OAuth2\Client\Provider\DocuSign;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

class DocuSignConfigurator implements ConfiguratorInterface, ClientSecretAwareConfiguratorInterface
{
    /**
     * Determine if client secret is requruired.
     */
    public function needsClientSecret(): bool
    {
        return true;
    }

    /**
     * Indicates if provider is supported by configurator.
     */
    public function supports(string $name): bool
    {
        return $name === DocuSign::class;
    }

    /**
     * Returns the provider options from configuratin.
     */
    public function getProviderOptions(array $config): array
    {
        return [
            'clientId'            => $config['client_id'] ?? null,
            'clientSecret'        => $config['client_secret'] ?? null,
            'authorizationServer' => $config['auth_server_url'] ?? null,
            'defaultResponseType' => $config['response_type'] ?? null,
            'allowSilentAuth'     => $config['silent_auth'] ?? null,
            'targetAccountId'     => $config['default_account_id'] ?? null,
            'redirectUri'         => $config['redirect_uri'] ?? null,
        ];
    }

    /**
     * Adds the configuration section for the provided configuration tree.
     *
     * @param ArrayNodeDefinition|NodeDefinition $node
     */
    public function addConfigurationSection(NodeDefinition $node): void
    {
        $node
            ->children()
                ->scalarNode('auth_server_url')
                    ->info('The DocuSign authorization service.')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(fn ($v) => false === filter_var($v, \FILTER_VALIDATE_URL))
                        ->thenInvalid('The "auth_server_url" must be a valid URI address.')
                    ->end()
                ->end()
                ->scalarNode('default_account_id')
                    ->info('The default DocuSign account ID.')
                    ->defaultNull()
                ->end()
                ->booleanNode('silent_auth')
                    ->info('The flag that indicates if silent authorization is allowed.')
                    ->defaultFalse()
                ->end()
            ->end()
        ;
    }
}
