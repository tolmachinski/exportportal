<?php

namespace App\Sitemap;

use samdark\sitemap\Sitemap;

class SitemapAdapter implements SitemapAdapterInterface
{
    private $sitemap;

    public function __construct(Sitemap $sitemap, $baseUrl = null)
    {
        $this->sitemap = $sitemap;
        $this->baseUrl = $baseUrl;
    }

    public function addItem(
        $location, 
        $lastModified = null, 
        $changeFrequency = null, 
        $priority = null
    ) {
        $this->sitemap->addItem($location, $lastModified, $changeFrequency, $priority);
    }

    public function generateFiles()
    {
        $this->sitemap->write();
    }

    public function getSitemapUrls()
    {
        return $this->sitemap->getSitemapUrls($this->baseUrl);
    }
}

