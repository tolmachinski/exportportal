<?php

use App\Common\Assets\EntrypointLookup;
use App\Common\Assets\EntrypointLookupCollection;
use App\Common\Assets\EntrypointLookupCollectionInterface;
use App\Common\Assets\EntrypointLookupInterface;
use App\Common\Assets\Html\TagRenderer;
use App\Common\Assets\Html\TagRendererInterface;
use App\Common\Assets\Html\WebLinkedTagRenderer;
use App\Common\Assets\Html\WebLinkedTagRendererInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\WebLink\GenericLinkProvider;

/**
 * Library Encore.
 *
 * @link https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/d.-Libraries/i.-Encore
 */
class TinyMVC_Library_Encore
{
	private const ENTRYPOINTS_FILE_NAME = 'entrypoints.json';

	private const CONFIG = 'encore.php';

	/**
	 * The request stack.
	 *
	 * @var RequestStack
	 */
	private $requestStack;

	/**
	 * The master request.
	 *
	 * @var null|Request
	 */
	private $masterRequest;

	/**
	 * The deafault build name, if exists,.
	 *
	 * @var null|string
	 */
	private $defaultBuildName;

	/**
	 * The tag renderer.
	 *
	 * @var TagRendererInterface[]
	 */
	private $tagRenderers = [];

	/**
	 * The name of the entrypoints file.
	 *
	 * @var string
	 */
	private $entrypointsFileName;

	/**
	 * The collection of entrypoints parsers.
	 *
	 * @var EntrypointLookupCollection
	 */
	private $entrypointsCollection;

	/**
	 * The list of rendered script entries.
	 */
	private $renderedScriptEntries = [];

	/**
	 * The list of rendered link entries.
	 */
	private $renderedLinkEntries = [];

	/**
	 * The block that will be replaced with rendered scripts.
	 *
	 * @var null|string
	 */
	private $scriptsBlockTag;

	/**
	 * The block that will be replaced with rendered links.
	 *
	 * @var null|string
	 */
	private $linksBlockTag;

	/**
	 * The flag that indicates if preload must be enabled.
	 *
	 * @var bool
	 */
	private $enabledPreload = false;

	/**
	 * The current preload options.
	 *
	 * @var array
	 */
	private $preloadOptions = [];

	/**
	 * The default preload options.
	 *
	 * @var array
	 */
	private $originalPreloadOptions = [];

	/**
	 * The deafult attributes.
	 *
	 * @var array
	 */
	private $defaultAttributes = [];

	/**
	 * The builds metadata.
	 *
	 * @var mixed[][]
	 */
	private $builds = [];

	/**
	 * The encore configs.
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
				require_once $path
			);
		}

		$this->requestStack = $container->get(RequestStack::class);
		$this->masterRequest = $this->requestStack->getMasterRequest();
		$this->entrypointsFileName = config('env.ENCORE_ENTRYPOINTS_FILE') ?? static::ENTRYPOINTS_FILE_NAME;
		$this->validateConfigs($configs);
		$this->boot(
			$this->configs = $configs
		);
	}

	/**
	 * Check if preload mode is enabled.
	 */
	public function isPreloadEnabled(): bool
	{
		return $this->enabledPreload;
	}

	/**
	 * Disable preload mode.
	 */
	public function disablePreload(): void
	{
		$this->enabledPreload = false;
	}

	/**
	 * Enable preload mode.
	 */
	public function enablePreload(): void
	{
		$this->enabledPreload = true;
	}

	/**
	 * Returns the encore configs.
	 */
	public function getConfigs(): array
	{
		return $this->configs;
	}

	/**
	 * Returns the tag renderer.
	 *
	 * @return TagRendererInterface|WebLinkedTagRendererInterface
	 */
	public function getTagRenderer(): TagRendererInterface
	{
		return $this->resolveTagRenderer();
	}

	/**
	 * Get the list of rendered script entries.
	 */
	public function getRenderedScriptEntries()
	{
		return $this->renderedScriptEntries;
	}

	/**
	 * Get the list of rendered link entries.
	 */
	public function getRenderedLinkEntries()
	{
		return $this->renderedLinkEntries;
	}

	/**
	 * Get the preload options.
	 */
	public function getPreloadOptions(): array
	{
		return $this->preloadOptions;
	}

	/**
	 * Reset preload options.
	 */
	public function resetPreloadOptions(): void
	{
		$this->changePreloadOptions($this->originalPreloadOptions);
	}

	/**
	 * Change preload options.
	 */
	public function changePreloadOptions(array $options = []): void
	{
		if (null !== $options['enable'] ?? null) {
			if ($options['enable']) {
				$this->enablePreload();
			} else {
				$this->disablePreload();
			}
		}

		$this->preloadOptions = array_merge($this->getDefaultPreloadOptions(), $options);
	}

	/**
	 * Add one rendered JS entry to the list of rendered script entries.
	 */
	public function addRenderedScriptEntry(string $entry)
	{
		$this->renderedScriptEntries[] = $entry;
	}

	/**
	 * Add one rendered CSS entry to the list of rendered link entries.
	 */
	public function addRenderedLinkEntry(string $entry)
	{
		$this->renderedLinkEntries[] = $entry;
	}

	/**
	 * The block that will be replaced with rendered scripts.
	 */
	public function getScriptsBlockTag(): string
	{
		if (null === $this->scriptsBlockTag) {
			$this->scriptsBlockTag = $this->generateBlock('script');
		}
		if (null !== $this->masterRequest) {
			$this->masterRequest->attributes->set('_encore.scripts.container', true);
		}

		return $this->scriptsBlockTag;
	}

	/**
	 * The block that will be replaced with rendered links.
	 */
	public function getLinksBlockTag(): string
	{
		if (null === $this->linksBlockTag) {
			$this->linksBlockTag = $this->generateBlock('links');
		}
		if (null !== $this->masterRequest) {
			$this->masterRequest->attributes->set('_encore.links.container', true);
		}

		return $this->linksBlockTag;
	}

	/**
	 * Get the collection of entrypoints parsers.
	 *
	 * @return EntrypointLookupCollection
	 */
	public function getEntrypointsCollection()
	{
		return $this->resolveEntrypoints();
	}

	/**
	 * Generates block for tags.
	 */
	private function generateBlock(string $type): string
	{
		$signature = hash('sha512', 'script-' . (new DateTimeImmutable())->format(DATE_ATOM));

		return "<encore-app-{$type}-{$signature}></encore-app-{$type}-{$signature}>";
	}

	/**
	 * Returns default set of configurations.
	 */
	private function getDefaultConfigs(): array
	{
		return [
			'output_path' => null,
			'crossorigin' => false,
			'strict_mode' => true,
			'preload'     => null,
			'builds'      => null,
			'cache'       => false,
			'cache_pool'  => null,
		];
	}

	/**
	 * Returns default set of configurations.
	 */
	private function getDefaultPreloadOptions(): array
	{
		return [
			'enable'     => false,
			'nopush'     => null,
			'importance' => null,
		];
	}

	/**
	 * Boots the Encore wrapper.
	 */
	private function boot(array $config): void
	{
		$builds = [];
		if ($config['output_path']) {
			$this->defaultBuildName = '_default';
			$builds[$this->defaultBuildName] = [
				$this->defaultBuildName,
				$config['output_path'],
				$config['strict_mode'] ?? false,
				$config['cache'] ?? false,
				$config['cache_pool'] ?? null,
			];
		}

		foreach ($config['builds'] ?? [] as $name => $path) {
			$builds[$name] = [
				$name,
				$path,
				$config['strict_mode'] ?? false,
				$config['cache'] ?? false,
				$config['cache_pool'] ?? false,
			];
		}

		$defaultAttributes = [];
		if (false !== $config['crossorigin']) {
			$defaultAttributes['crossorigin'] = $config['crossorigin'];
		}

		$this->builds = $builds;
		$this->defaultAttributes = $defaultAttributes;
		$this->originalPreloadOptions = $this->preloadOptions = array_merge($this->getDefaultPreloadOptions(), $config['preload'] ?? []);
		if ($this->preloadOptions['enable'] ?? false) {
			$this->enabledPreload = true;
		}
	}

	/**
	 * Creates the entrypoint lookup instance.
	 */
	private function createEntrypointLookup(
		string $name,
		string $path,
		bool $strictMode,
		bool $cacheEnabled,
		?string $cachePool
	): EntrypointLookupInterface {
		return new EntrypointLookup(
			"{$path}/{$this->entrypointsFileName}",
			$cacheEnabled ? $this->resolveCachePool($cachePool) : null,
			$name,
			$strictMode,
		);
	}

	/**
	 * Resolves the assets packages.
	 */
	private function resolveAssetsPackages(): Packages
	{
		/** @var TinyMVC_Library_Assets $assets */
		$assets = library(TinyMVC_Library_Assets::class);

		return $assets->getPackages();
	}

	/**
	 * Resolves the cache pool.
	 */
	private function resolveCachePool(string $poolName): CacheItemPoolInterface
	{
		/** @var TinyMVC_Library_Fastcache $cacheHandler */
		$cacheHandler = library(TinyMVC_Library_Fastcache::class);

		return $cacheHandler->pool($poolName);
	}

	/**
	 * resolve entrypoints collection.
	 */
	private function resolveEntrypoints(): EntrypointLookupCollectionInterface
	{
		if (null === $this->entrypointsCollection) {
			$builds = [];
			foreach ($this->builds as $name => $args) {
				$builds[$name] = $this->createEntrypointLookup(...$args);
			}

			$this->entrypointsCollection = new EntrypointLookupCollection(
				new ArrayCollection($builds),
				$this->defaultBuildName
			);
		}

		return $this->entrypointsCollection;
	}

	/**
	 * Resolve tag renderer instance.
	 */
	private function resolveTagRenderer(): TagRendererInterface
	{
		if (!$this->enabledPreload) {
			if (null === ($this->tagRenderers['_base'] ?? null)) {
				$this->tagRenderers['_base'] = $this->createBaseTagRenderer($this->defaultAttributes);
			}

			return $this->tagRenderers['_base'];
		}

		if (null === $this->tagRenderers['_linked'] ?? null) {
			$this->tagRenderers['_linked'] = $this->createLinkedTagRenderer(
				$this->preloadOptions,
				$this->defaultAttributes
			);
		}
		if ($this->originalPreloadOptions !== $this->preloadOptions) {
			/** @var WebLinkedTagRendererInterface $webLinkedRenderer */
			$webLinkedRenderer = $this->tagRenderers['_linked'];
			$webLinkedRenderer->changeImportance($this->preloadOptions['importance'] ?? null);
			$webLinkedRenderer->changeNopushMode($this->preloadOptions['nopush'] ?? false);
		}

		return $this->tagRenderers['_linked'];
	}

	/**
	 * Create base tag renderer.
	 */
	private function createBaseTagRenderer(array $attributes = []): TagRendererInterface
	{
		return new TagRenderer(
			$this->resolveEntrypoints(),
			$this->requestStack,
			$this->resolveAssetsPackages(),
			$attributes
		);
	}

	/**
	 * Create linked tag renderer.
	 */
	private function createLinkedTagRenderer(array $options = [], array $attributes = []): WebLinkedTagRendererInterface
	{
		if (!class_exists(GenericLinkProvider::class)) {
			throw new \LogicException('To use the "preload" option, the WebLink component must be installed. Try running "composer require symfony/web-link".');
		}

		return new WebLinkedTagRenderer(
			$this->resolveEntrypoints(),
			$this->requestStack,
			$this->resolveAssetsPackages(),
			$attributes,
			$options['importance'] ?? null,
			$options['nopush'] ?? null,
		);
	}

	/**
	 * Validates the configurations.
	 *
	 * @throws InvalidArgumentException if invalid
	 */
	private function validateConfigs(array $configs): void
	{
		if (!isset($configs['output_path'])) {
			throw new InvalidArgumentException(
				sprintf('The parameter "%s" is required.', 'output_path')
			);
		}
		if (false === $configs['output_path'] && empty($configs['builds'])) {
			throw new InvalidArgumentException('Default build can only be disabled if multiple entry points are defined.');
		}
		if (!in_array($configs['crossorigin'], [false, 'anonymous', 'use-credentials'])) {
			throw new InvalidArgumentException(
				sprintf('The parameter "%s" must be one of: %s, "%s" or "%s".', 'output_path', 'false', 'anonymous', 'use-credentials')
			);
		}
		if (isset($configs['builds'])) {
			if (!is_array($configs['builds'])) {
				throw new InvalidArgumentException(
					sprintf('The parameter "%s" must be an array', 'builds')
				);
			}

			if (isset($configs['builds']['_default'])) {
				throw new InvalidArgumentException("Key '_default' can't be used as build name.");
			}
		}
	}
}

// End of file tinymvc_library_encore.php
// Location: /tinymvc/myapp/plugins/tinymvc_library_encore.php
