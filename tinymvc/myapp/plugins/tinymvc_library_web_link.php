<?php

declare(strict_types=1);

use Psr\Link\EvolvableLinkProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\WebLink\GenericLinkProvider;
use Symfony\Component\WebLink\Link;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [03.12.2021]
 * library refactoring: using deprecated method
 */
class TinyMVC_Library_Web_Link
{
    /**
     * The links provider.
     *
     * @var EvolvableLinkProviderInterface
     */
    private $linksProvider;

    /**
     * The request stack.
     *
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param ContainerInterface $container The container instance
     */
    public function __construct(ContainerInterface $container)
    {
        $this->requestStack = $container->get(RequestStack::class);
        $this->linksProvider = new GenericLinkProvider();
    }

    /**
     * Get the links provider.
     */
    public function getLinksProvider(): EvolvableLinkProviderInterface
    {
        return $this->linksProvider;
    }

    /**
     * Adds a "Link" HTTP header.
     *
     * @param string $uri        The relation URI
     * @param string $rel        The relation type (e.g. "preload", "prefetch", "prerender" or "dns-prefetch")
     * @param array  $attributes The attributes of this link (e.g. "['as' => true]", "['pr' => 0.5]")
     *
     * @return string The relation URI
     */
    public function link($uri, $rel, array $attributes = [])
    {
        if (!$request = $this->requestStack->getMasterRequest()) {
            return $uri;
        }

        $link = new Link($rel, $uri);
        foreach ($attributes as $key => $value) {
            $link = $link->withAttribute($key, $value);
        }

        $linkProvider = $this->getLinksProvider();
        $request->attributes->set('_links', $this->linksProvider = $linkProvider->withLink($link));

        return $uri;
    }
}

// End of file tinymvc_library_web_link.php
// Location: /tinymvc/myapp/plugins/tinymvc_library_web_link.php
