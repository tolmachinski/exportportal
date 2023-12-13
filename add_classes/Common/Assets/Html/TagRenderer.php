<?php

declare(strict_types=1);

namespace App\Common\Assets\Html;

use App\Common\Assets\AttributesProviderInterface;
use App\Common\Assets\EntrypointLookupCollectionInterface;
use App\Common\Assets\EntrypointLookupInterface;
use App\Common\Assets\IntegrityDataProviderInterface;
use App\Common\Assets\ServiceResetInterface;
use App\Common\Exceptions\FileNotFoundException;
use Generator;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\RequestStack;

class TagRenderer implements TagRendererInterface, AttributesProviderInterface, ServiceResetInterface
{
    /**
     * The request stack.
     *
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * The entrypoints finder collection.
     *
     * @var EntrypointLookupCollectionInterface
     */
    private $lookupCollection;

    /**
     * The assets packages.
     *
     * @var Packages
     */
    private $packages;

    /**
     * The list of default attributes.
     *
     * @var array
     */
    private $defaultAttributes;

    /**
     * The set of the rendered scripts.
     *
     * @var script[]
     */
    private $renderedScripts = array();

    /**
     * The set of the rendered styles.
     *
     * @var script[]
     */
    private $renderedStyles = array();

    /**
     * Creates instance of tag renderer.
     */
    public function __construct(
        EntrypointLookupCollectionInterface $lookupCollection,
        RequestStack $requestStack,
        Packages $packages,
        array $defaultAttributes = array()
    ) {
        $this->packages = $packages;
        $this->requestStack = $requestStack;
        $this->defaultAttributes = $defaultAttributes;
        $this->lookupCollection = $lookupCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderedScripts(): array
    {
        return $this->renderedScripts;
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderedStyles(): array
    {
        return $this->renderedStyles;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultAttributes(): array
    {
        return $this->defaultAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultAttributes(array $attributes): void
    {
        $this->defaultAttributes = $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function mergeDefaultAttributes(array $attributes): void
    {
        $this->defaultAttributes = array(
            $this->defaultAttributes,
            $attributes,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function resetFiles(): void
    {
        $this->renderedScripts = array();
        $this->renderedStyles = array();
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        $this->resetFiles();
        $this->defaultAttributes = array();
    }

    /**
     * {@inheritdoc}
     */
    public function renderScriptTags(
        string $entryName,
        string $entrypointName = '_default',
        ?string $packageName = null,
        ?array $attributes = array(),
        bool $inline = false,
        bool $escape = true
    ): string {
        $tags = array();
        $entrypointFinder = $this->getEntrypointFinder($entrypointName);
        $integrityHashes = ($entrypointFinder instanceof IntegrityDataProviderInterface)
            ? $entrypointFinder->getIntegrityData()
            : array();

        foreach ($entrypointFinder->getJSFiles($entryName) as $filename) {
            list($scriptPath, $scriptAttributes, $tag) = \iterator_to_array(
                $this->processScript($filename, $packageName, $integrityHashes, \array_merge($this->defaultAttributes, $attributes ?? array()), $inline, $escape)
            );

            $tags[] = $tag;
            $this->renderedScripts[] = $scriptPath;
        }
        if ($request = $this->requestStack->getMasterRequest()) {
            $request->attributes->set('_encore.scripts', true);
        }

        return implode('', $tags);
    }

    /**
     * {@inheritdoc}
     */
    public function renderLinkTags(
        string $entryName,
        string $entrypointName = '_default',
        ?string $packageName = null,
        ?array $attributes = array(),
        bool $inline = false,
        bool $escape = true
    ): string {
        $tags = array();
        $entrypointFinder = $this->getEntrypointFinder($entrypointName);
        $integrityHashes = ($entrypointFinder instanceof IntegrityDataProviderInterface)
            ? $entrypointFinder->getIntegrityData()
            : array();

        foreach ($entrypointFinder->getCssFiles($entryName) as $filename) {
            list($linkPath, $linkAttributes, $tag) = \iterator_to_array(
                $this->processLink($filename, $packageName, $integrityHashes, \array_merge($this->defaultAttributes, $attributes ?? array()), $inline, $escape)
            );

            $tags[] = $tag;
            $this->renderedStyles[] = $linkPath;
        }
        if ($request = $this->requestStack->getMasterRequest()) {
            $request->attributes->set('_encore.links', true);
        }

        return implode('', $tags);
    }

    /**
     * Process script resource.
     */
    protected function processScript(
        string $filename,
        ?string $packageName,
        array $integrityHashes = array(),
        array $attributes = array(),
        bool $inline = false,
        bool $escape = true
    ): Generator {
        $scriptAttributes = $attributes;
        $scriptContent = '';
        $scriptPath = $this->getAssetPath($filename, $packageName);

        yield $scriptPath;

        if ($inline) {
            try {
                $scriptContent = $this->getFileContents($scriptPath);
                if ($escape) {
                    $scriptContent = "/*<![CDATA[*/ {$scriptContent} /*]]>*/";
                }

                unset($scriptAttributes['crossorigin']);
            } catch (FileNotFoundException $exception) {
                // If file was not found or it exists on remote CDN - just use is like 'src'
                $scriptAttributes['src'] = $scriptPath;
                $inline = false;
            }
        } else {
            $scriptAttributes['src'] = $scriptPath;
        }
        if (isset($integrityHashes[$filename])) {
            $scriptAttributes['integrity'] = $integrityHashes[$filename];
        }

        yield $scriptAttributes;
        yield sprintf('<script %s>%s</script>', $this->makeAttributesFromArray($scriptAttributes), $scriptContent);
    }

    /**
     * Process link resource.
     */
    protected function processLink(
        string $filename,
        ?string $packageName,
        array $integrityHashes = array(),
        array $attributes = array(),
        bool $inline = false,
        bool $escape = true
    ): Generator {
        $linkAttributes = \array_merge($this->defaultAttributes, $attributes ?? array());
        $linkContent = '';
        $linkPath = $this->getAssetPath($filename, $packageName);

        yield $linkPath;

        if ($inline) {
            try {
                $linkContent = $this->getFileContents($linkPath);
                if ($escape) {
                    $linkContent = "/*<![CDATA[*/ {$linkContent} /*]]>*/";
                }

                unset($linkAttributes['crossorigin']);
                $linkAttributes['type'] = 'text/css';

                yield $linkAttributes;
                yield sprintf('<style %s>%s</style>', $this->makeAttributesFromArray($linkAttributes), $linkContent);

                return;
            } catch (FileNotFoundException $exception) {
                // If file was not found or it exists on remote CDN
                // fallback to the LINK tags
            }
        }

        $linkAttributes['rel'] = 'stylesheet';
        $linkAttributes['href'] = $linkPath;
        if (isset($integrityHashes[$filename]) && !$inline) {
            $linkAttributes['integrity'] = $integrityHashes[$filename];
        }

        yield $linkAttributes;
        yield sprintf('<link %s>', $this->makeAttributesFromArray($linkAttributes));
    }

    /**
     * Get asset path.
     */
    private function getAssetPath(string $assetPath, string $packageName = null): string
    {
        if (null === $this->packages) {
            throw new \Exception('To render the script or link tags, run "composer require symfony/asset".');
        }

        return $this->packages->getUrl(
            $assetPath,
            $packageName
        );
    }

    /**
     * Get finder.
     */
    private function getEntrypointFinder(string $buildName): EntrypointLookupInterface
    {
        return $this->lookupCollection->getEntrypointLookup($buildName);
    }

    /**
     * Makes attributes from array.
     */
    private function makeAttributesFromArray(array $attributesMap): string
    {
        return implode(' ', array_map(
            function ($key, $value) { return sprintf('%s="%s"', $key, htmlentities((string) $value)); },
            array_keys($attributesMap),
            $attributesMap
        ));
    }

    /**
     * Returns the file contents.
     *
     * @throws FileNotFoundException if file with provided path is not found
     */
    private function getFileContents(string $path): string
    {
        $isUrl = \filter_var($path, \FILTER_VALIDATE_URL);
        if ($isUrl) {
            $path = \parse_url($path, PHP_URL_PATH) ?? '/';
        }

        $filepath = \realpath(\ltrim($path, '/\\'));
        if (\false === $filepath || !\file_exists($filepath)) {
            throw new FileNotFoundException($filepath);
        }

        return \file_get_contents($filepath) ?: '';
    }
}
