<?php

declare(strict_types=1);

use App\Common\Exceptions\NotFoundException;
use App\Common\OAuth2\Client\Client;
use App\Common\OAuth2\Client\ClientInterface;
use App\Common\OAuth2\Client\ClientResolverInterface;
use App\Common\OAuth2\Client\Configuration as ClientConfiguration;
use App\Common\OAuth2\Client\Provider\Configurator\ConfiguratorInterface;
use App\Common\OAuth2\Client\Provider\ProviderFactoryInterface;
use App\Common\OAuth2\Client\Token\Storage\StorageInterface;
use App\Common\OAuth2\Configuration;
use App\OAuth2\Client\Provider\Configurator\DocuSignConfigurator;
use App\OAuth2\Client\Provider\DocuSignFactory;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;

/**
 * Library Oauth2.
 */
class TinyMVC_Library_Oauth2 implements ClientResolverInterface
{
    private const CONFIG_FILE = 'oauth2.php';

    /**
     * The list of known provider factories.
     *
     * @var string[]
     */
    private array $factories = [
        DocuSignFactory::class,
    ];

    /**
     * The list of known provider configurators.
     *
     * @var string[]
     */
    private array $configurators = [
        DocuSignConfigurator::class,
    ];

    /**
     * The list of clients.
     *
     * @var ClientInterface[]|mixed[][]
     */
    private array $clients = [];

    /**
     * The request stack.
     */
    private RequestStack $requestStack;

    /**
     * The configurations.
     */
    private Configuration $configurations;

    /**
     * The list of provider factories.
     *
     * @var ProviderFactoryInterface[]
     */
    private array $resolvedFactories = [];

    /**
     * The list of provider configurators instances.
     *
     * @var ConfiguratorInterface[]
     */
    private array $resolvedConfigurators = [];

    /**
     * The OAuth2 configurations.
     */
    private array $configs;

    /**
     * Library Oauth2 constructor.
     */
    public function __construct()
    {
        $fileLocator = new FileLocator([\App\Common\CONFIG_PATH]);
        $phpConfigFile = $fileLocator->locate(static::CONFIG_FILE, null, true);
        $this->configurations = new Configuration();
        $this->configs = (new Processor())->processConfiguration(
            $this->configurations,
            (function () use ($phpConfigFile): array {
                if (!file_exists($phpConfigFile)) {
                    return ['oauth2' => []];
                }

                return ['oauth2' => include $phpConfigFile];
            })()
        );

        foreach ($this->configs['configurators'] ?? [] as $configurator) {
            $this->configurators[] = new $configurator();
        }
        foreach ($this->configs['factories'] ?? [] as $factory) {
            $this->factories[] = new $factory();
        }
        foreach ($this->configs['clients'] ?? [] as $clientName => $options) {
            $this->clients[$clientName] = $options;
        }
        $this->requestStack = requestStack();
        $this->ensureSessionExistsInRequest($this->requestStack);
    }

    /**
     * Get the value of configs.
     */
    public function getConfigs(): array
    {
        return $this->configs;
    }

    /**
     * Get the configuration for the client.
     */
    public function getClientConfigs(string $providerName): array
    {
        return $this->configs['clients'][$providerName] ?? [];
    }

    /**
     * Get the OAuth2 client.
     *
     * @throws NotFoundException if client with this types is not found
     */
    public function client(string $type): ClientInterface
    {
        if (!isset($this->clients[$type])) {
            throw new NotFoundException('The client with the provided type is not found');
        }

        $clientDefinition = $this->clients[$type];
        if (is_array($clientDefinition)) {
            if (null === $client = $this->makeClient($type, $clientDefinition)) {
                throw new RuntimeException('Failed to create the OAuth2 client.');
            }

            $this->clients[$type] = $client;
        }

        return $this->clients[$type];
    }

    /**
     * Returns the provider factories.
     */
    protected function getProviderFactories(): array
    {
        if (empty($this->resolvedFactories)) {
            foreach ($this->factories as $factory) {
                $this->resolvedFactories[] = new $factory();
            }
        }

        return $this->resolvedFactories;
    }

    /**
     * Returns the provider configurators.
     */
    protected function getProviderConfigurators(): array
    {
        if (empty($this->resolvedConfigurators)) {
            foreach ($this->configurators as $configurator) {
                $this->resolvedConfigurators[] = new $configurator();
            }
        }

        return $this->resolvedConfigurators;
    }

    /**
     * Makes the client from provided options.
     */
    private function makeClient(string $name, array $options): ?ClientInterface
    {
        $providerName = $options['provider']['type'];
        /** @var ProviderFactoryInterface $factory */
        foreach ($this->findFactoriesForType($providerName) as $factory) {
            $combinedClientConfigs = [];
            $combinedProviderOptions = [];
            foreach ($this->findConfiguratorForType($providerName) as $configurator) {
                $clientConfigs = (new Processor())->processConfiguration(new ClientConfiguration("oauth2/clients/{$name}", $configurator), [$options]);
                $providerOptions = $configurator->getProviderOptions($clientConfigs['provider']);
                $combinedClientConfigs = array_merge($combinedClientConfigs, $clientConfigs);
                $combinedProviderOptions = array_merge($combinedProviderOptions, $providerOptions);
            }
            // Create provider
            $provider = $factory->create($providerOptions);
            if (null === $provider) {
                continue;
            }

            // Create storage
            $storageConfigs = $combinedClientConfigs['storage'] ?? [];
            $storage = null;
            if (null !== $storageConfigs) {
                list('type' => $storageType, 'options' => $storageOptions) = $storageConfigs;
                /** @var StorageInterface $storage */
                $storage = new $storageType();
                if (!empty($storageOptions)) {
                    $storage->updateOptions($storageOptions);
                }
            }

            return new Client(
                $provider,
                $this->requestStack,
                $storage,
                $clientConfigs['grant_type'] ?? null,
                $clientConfigs['client_scopes'] ?? [],
                $clientConfigs['stateless'] ?? false
            );
        }

        return null;
    }

    /**
     * Finds the supported provider factories by its name.
     */
    private function findFactoriesForType(string $providerType): Generator
    {
        yield from array_filter(
            $this->getProviderFactories(),
            fn (ProviderFactoryInterface $factory) => $factory->supports($providerType)
        );
    }

    /**
     * Finds the supported provider configurators by its name.
     */
    private function findConfiguratorForType(string $providerType): Generator
    {
        yield from array_filter(
            $this->getProviderConfigurators(),
            fn (ConfiguratorInterface $configurator) => $configurator->supports($providerType)
        );
    }

    /**
     * Ensures that the session exists in the request.
     */
    private function ensureSessionExistsInRequest(RequestStack $requestStack): void
    {
        $request = $requestStack->getCurrentRequest();
        if (null === $request) {
            return;
        }

        if (!$request->hasSession()) {
            $request->setSession(new Session(new PhpBridgeSessionStorage()));
        }
    }
}

// End of file tinymvc_library_oauth2.php
// Location: /tinymvc/myapp/plugins/tinymvc_library_oauth2.php
