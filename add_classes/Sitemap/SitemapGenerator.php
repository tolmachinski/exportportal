<?php

namespace App\Sitemap;

class SitemapGenerator implements SitemapGeneratorInterface
{
    private $urlsCollection = null;
    private $sitemapIndex = null;
    private $sitemap = null;
    private $frequency = null;
    private $priority = null;
    private $sitemapFilesUrls = array();

    public function __construct(
        UrlSourceInterface $urlsSource,
        SitemapAdapterInterface $sitemap,
        SitemapIndexAdapterInterface $sitemapIndex = null,
        $frequency = SELF::WEEKLY,
        $priority = 0.6
    ) {
        $this->urlsCollection = $urlsSource;
        $this->sitemapIndex = $sitemapIndex;
        $this->sitemap = $sitemap;
        $this->frequency = $frequency;
        $this->priority = $priority;
    }

    public function generate()
    {
        foreach ($this->urlsCollection->getUrls() as $url) {
            $lastModified = time();

            if (is_array($url)) {
                list($url, $lastModified) = $url;
            }

            $this->sitemap->addItem($url, $lastModified, $this->frequency, $this->priority);
        }

        $this->sitemap->generateFiles();

        if (null !== $this->sitemapIndex) {
            foreach ($this->sitemap->getSitemapUrls() as $sitemapUrl) {
                $this->sitemapIndex->addSitemap($sitemapUrl, time());
                $this->sitemapFilesUrls[] = $sitemapUrl;
            }

            $this->sitemapIndex->generateFile();
        } else {
            foreach ($this->sitemap->getSitemapUrls() as $url) {
                $this->sitemapFilesUrls[] = $url;
            }
        }
    }

    public function getSitemapFilesUrls(): array
    {
        return $this->sitemapFilesUrls;
    }
}
