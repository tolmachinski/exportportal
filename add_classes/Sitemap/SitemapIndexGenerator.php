<?php

namespace App\Sitemap;

class SitemapIndexGenerator implements SitemapGeneratorInterface
{
    private $urlsCollection = null;
    private $sitemapIndex = null;

    public function __construct(
        UrlSourceInterface $urlsSource,
        SitemapIndexAdapterInterface $sitemapIndex
    ) {
        $this->urlsCollection = $urlsSource;
        $this->sitemapIndex = $sitemapIndex;
    }

    public function generate()
    {
        foreach ($this->urlsCollection->getUrls() as $url) {
            $this->sitemapIndex->addSitemap($url);
        }

        $this->sitemapIndex->generateFile();
    }
}