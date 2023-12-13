<?php

use Symfony\Component\Asset\Context\ContextInterface;
use Symfony\Component\Asset\Context\NullContext;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\Asset\PackageInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\RemoteJsonManifestVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Assets library.
 *
 * @link https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/d.-Libraries/c.-Assets
 */
class TinyMVC_Library_Assets
{
    private const CONFIG = 'assets.php';

    /**
     * The request stack.
     *
     * @var RequestStack
     */
    private $requestStack;

    /**
     * The list of definitions.
     *
     * @var array
     */
    private $definitions = [];

    /**
     * The assets context.
     *
     * @var ContextInterface
     */
    private $assetsContext;

    /**
     * The assets packages.
     *
     * @var Packages
     */
    private $packages;

    /**
     * The assets configs.
     *
     * @var array
     */
    private $configs;

    /**
     * @param ContainerInterface $container The container instance
     */
    public function __construct(ContainerInterface $container)
    {
        $configs = $this->getDefaultConfigs();
        if (file_exists($path = realpath(\App\Common\CONFIG_PATH . '/' . static::CONFIG))) {
            $configs = array_merge(
                $configs,
                require $path
            );
        }

        $this->requestStack = $container->get(RequestStack::class);
        if ($request = $this->requestStack->getMasterRequest()) {
            $request->attributes->set('_assets', true);
        }

        $this->resgisterAssetsConfigurations(
            $this->configs = $configs,
            $this->assetsContext = $this->createPackageContext($this->requestStack, $configs)
        );
    }

    /**
     * Returns the assets configs.
     */
    public function getConfigs(): array
    {
        return $this->configs;
    }

    /**
     * Returns the assets packages.
     */
    public function getPackages(): Packages
    {
        return $this->packages;
    }

    /**
     * Returns the assets package context.
     */
    public function getPackageContext(): ContextInterface
    {
        return $this->assetsContext;
    }

    /**
     * Returns default set of configurations.
     */
    private function getDefaultConfigs(): array
    {
        return [
            'base_path'          => '',
            'base_urls'          => null,
            'packages'           => [],
            'version'            => null,
            'version_format'     => '%%s?%%s',
            'version_strategy'   => null,
            'json_manifest_path' => null,
        ];
    }

    /**
     * Registers assets configuration.
     */
    private function resgisterAssetsConfigurations(array $config, ContextInterface $context): void
    {
        $this->validateConfigs($config);

        if ($config['version_strategy']) {
            $defaultVersion = $this->resolveVersion($config['version_strategy'], $config, 'version_strategy');
        } else {
            $defaultVersion = $this->createVersion($config['version'], $config['version_format'], $config['json_manifest_path']);
        }
        $defaultPackage = $this->createPackageDefinition($defaultVersion, $config['base_path'] ?? null, $config['base_urls'] ?? null, $context);

        $this->registerVersion('_default', $defaultVersion);
        $this->registerPackage('_default', $defaultPackage);

        $namedPackages = [];
        foreach ($config['packages'] as $name => $package) {
            $this->validateConfigs($package);

            if (null !== $package['version_strategy']) {
                $version = $this->resolveVersion($package['version_strategy'], $package, "packages.{$name}.version_strategy");
            } elseif (!\array_key_exists('version', $package) && null === $package['json_manifest_path']) {
                // if neither version nor json_manifest_path are specified, use the default
                $version = $defaultVersion;
            } else {
                // let format fallback to main version_format
                $format = $package['version_format'] ?: $config['version_format'];
                $version = isset($package['version']) ? $package['version'] : null;
                $version = $this->createVersion($version, $format, $package['json_manifest_path']);
            }

            $this->registerVersion($name, $version);
            $this->registerPackage(
                $name,
                $namedPackages[$name] = $this->createPackageDefinition($version, $package['base_path'] ?? null, $package['base_urls'] ?? null, $context)
            );
        }

        $this->packages = new Packages(
            $defaultPackage,
            $namedPackages
        );
    }

    /**
     * Creates the package context.
     */
    private function createPackageContext(RequestStack $requestStack, array $config = []): ContextInterface
    {
        $request = $requestStack->getMainRequest();
        if ($request) {
            return new RequestStackContext(
                $requestStack,
                $config['request_context']['base_path'] ?? ($request ? $request->getBaseUrl() : null),
                $config['request_context']['secure'] ?? ($request ? $request->isSecure() : false)
            );
        }

        return new NullContext();
    }

    /**
     * Returns a definition for an asset version.
     */
    private function createVersion(?string $version, ?string $format, ?string $jsonManifestPath): VersionStrategyInterface
    {
        if (null !== $version) {
            return new StaticVersionStrategy($version, $format);
        }

        if (null !== $jsonManifestPath) {
            $strategyClassName = JsonManifestVersionStrategy::class;
            if (0 === \strpos(\parse_url($jsonManifestPath, PHP_URL_SCHEME), 'http')) {
                $strategyClassName = RemoteJsonManifestVersionStrategy::class;
            }

            return new $strategyClassName($jsonManifestPath);
        }

        return new EmptyVersionStrategy();
    }

    /**
     * Resolves version from instance or callable.
     *
     * @param mixed $version
     */
    private function resolveVersion($version, array $configs = [], string $path = null): VersionStrategyInterface
    {
        if ($version instanceof VersionStrategyInterface) {
            return $version;
        }

        if (is_callable($version)) {
            $version = $version($this, $configs);
        }

        if (!$version instanceof VersionStrategyInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    'The paramter "%s" must be instance of "%s" interface or a callbale that return the instance of that interface.',
                    $path,
                    VersionStrategyInterface::class
                )
            );
        }

        return $version;
    }

    /**
     * Returns a definition for an asset package.
     */
    private function createPackageDefinition(
        VersionStrategyInterface $version,
        ?string $basePath = null,
        ?array $baseUrls = null,
        ?ContextInterface $context = null
    ): PackageInterface {
        if ($basePath && $baseUrls) {
            throw new \LogicException('An asset package cannot have base URLs and base paths.');
        }

        if ($baseUrls) {
            return new UrlPackage($baseUrls, $version, $context);
        }

        return new PathPackage($basePath ?? '', $version, $context);
    }

    /**
     * Registers the definition for an asset version.
     */
    private function registerVersion(string $name, VersionStrategyInterface $version): void
    {
        $this->definitions["version.{$name}"] = $version;
    }

    /**
     * Registers the definition for an asset package.
     */
    private function registerPackage(string $name, PackageInterface $package): void
    {
        $this->definitions["packages.{$name}"] = $package;
    }

    /**
     * Validates the configurations.
     *
     * @throws InvalidArgumentException if invalid
     */
    private function validateConfigs(array $configs): void
    {
        if (isset($configs['version_strategy'], $configs['version'])) {
            throw new InvalidArgumentException('You cannot use both "version_strategy" and "version" at the same time.');
        }
        if (isset($configs['version_strategy'], $configs['json_manifest_path'])) {
            throw new InvalidArgumentException('You cannot use both "version_strategy" and "json_manifest_path" at the same time.');
        }
        if (isset($configs['version'], $configs['json_manifest_path'])) {
            throw new InvalidArgumentException('You cannot use both "version" and "json_manifest_path" at the same time.');
        }
        if (isset($configs['packages'])) {
            if (!is_array($configs['packages'])) {
                throw new InvalidArgumentException(
                    sprintf('The parameter "%s" must be an array', 'packages')
                );
            }
        }
        if (isset($configs['base_urls']) && empty($configs['base_urls'])) {
            if (!is_array($configs['base_urls'])) {
                throw new InvalidArgumentException(
                    sprintf('The parameter "%s" must be an array', 'base_urls')
                );
            }
            if (empty($configs['base_urls'])) {
                throw new InvalidArgumentException(
                    sprintf('The parameter "%s" must have at least one record', 'base_urls')
                );
            }
        }
    }
}

// End of file tinymvc_library_assets.php
// Location: /tinymvc/myapp/plugins/tinymvc_library_assets.php
