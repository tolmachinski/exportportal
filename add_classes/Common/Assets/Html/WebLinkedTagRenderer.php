<?php

declare(strict_types=1);

namespace App\Common\Assets\Html;

use App\Common\Assets\EntrypointLookupCollectionInterface;
use Generator;
use Psr\Link\EvolvableLinkProviderInterface;
use Psr\Link\LinkInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\WebLink\GenericLinkProvider;
use Symfony\Component\WebLink\Link;

final class WebLinkedTagRenderer extends TagRenderer implements WebLinkedTagRendererInterface
{
    /**
     * The master request instance.
     *
     * @var null|Request
     */
    private $masterRequest;

    /**
     * The preload importance.
     *
     * @var null|string
     */
    private $importance;

    /**
     * Indicates if NOPUSH mode must be enabled on server.
     *
     * @var bool
     */
    private $nopush;

    /**
     * The list of already preloaded resources.
     *
     * @var array
     */
    private $preloadedResources = array();

    /**
     * {@inheritdoc}
     *
     * @param null|string $importance The preload importance
     * @param null|bool   $nopush     Indicates if NOPUSH mode must be enabled on server
     */
    public function __construct(
        EntrypointLookupCollectionInterface $lookupCollection,
        RequestStack $requestStack,
        Packages $packages,
        array $defaultAttributes = array(),
        ?string $importance = null,
        ?bool $nopush = false
    ) {
        parent::__construct($lookupCollection, $requestStack, $packages, $defaultAttributes);

        $this->masterRequest = $this->requestStack->getMasterRequest();
        $this->importance = $importance ?? null;
        $this->nopush = $nopush ?? false;
    }

    /**
     * {@inheritdoc}
     */
    public function changeImportance(?string $importance): WebLinkedTagRendererInterface
    {
        $this->importance = $importance;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function changeNopushMode(bool $nopush): WebLinkedTagRendererInterface
    {
        $this->nopush = $nopush;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function processScript(
        string $filename,
        ?string $packageName,
        array $integrityHashes = array(),
        array $attributes = array(),
        bool $inline = false,
        bool $escape = true
    ): Generator {
        $metadata = parent::processScript($filename, $packageName, $integrityHashes, $attributes, $inline, $escape);
        if (!$this->masterRequest || $inline) {
            return $metadata;
        }

        /** @var EvolvableLinkProviderInterface $linkProvider */
        $linkProvider = $this->masterRequest->attributes->get('_links') ?? new GenericLinkProvider();
        list($src, $attributes, $tag) = \iterator_to_array($metadata);
        if (!isset($this->preloadedResources[$src])) {
            $linkProvider = $linkProvider->withLink(
                $this->getResourceLink($src, 'script', $attributes)
            );
        }
        $this->masterRequest->attributes->set('_links', $linkProvider);

        yield from array($src, $attributes, $tag);
    }

    /**
     * {@inheritdoc}
     */
    protected function processLink(
        string $filename,
        ?string $packageName,
        array $integrityHashes = array(),
        array $attributes = array(),
        bool $inline = false,
        bool $escape = true
    ): Generator {
        $metadata = parent::processLink($filename, $packageName, $integrityHashes, $attributes, $inline, $escape);
        if (!$this->masterRequest || $inline) {
            return $metadata;
        }

        /** @var EvolvableLinkProviderInterface $linkProvider */
        $linkProvider = $this->masterRequest->attributes->get('_links') ?? new GenericLinkProvider();
        list($href, $attributes, $tag) = \iterator_to_array($metadata);
        if (!isset($this->preloadedResources[$href])) {
            $linkProvider = $linkProvider->withLink(
                $this->getResourceLink($href, 'style', $attributes)
            );
        }
        $this->masterRequest->attributes->set('_links', $linkProvider);

        yield from array($href, $attributes, $tag);
    }

    /**
     * Makes resource prelodable.
     */
    private function getResourceLink(string $href, string $type, array $attributes = array()): LinkInterface
    {
        $link = (new Link('preload', $href))->withAttribute('as', $type);
        // Crossorigin
        if (false !== ($crossOrigin = $attributes['crossorigin'] ?? false)) {
            $link = $link->withAttribute('crossorigin', $crossOrigin);
        }
        // Integrity
        if (false !== ($integrity = $attributes['integrity'] ?? false)) {
            $link = $link->withAttribute('integrity', $integrity);
        }
        // Nopush
        if ($this->nopush) {
            $link = $link->withAttribute('nopush', $this->nopush);
        }
        // Importance
        if (null !== $this->importance) {
            $link = $link->withAttribute('importance', $this->importance);
        }

        return $link;
    }
}
