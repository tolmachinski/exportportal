<?php

namespace App\Sitemap;

use samdark\sitemap\Index;
use samdark\sitemap\Sitemap;

class SitemapIndexAdapter implements SitemapIndexAdapterInterface
{
    private $sitemapIndex;
    private $sitemap;

    public function __construct(Index $sitemapIndex, Sitemap $sitemap = null)
    {
        $this->sitemapIndex = $sitemapIndex;
        $this->sitemap = $sitemap;
    }

    public function addSitemap($location, $lastModified = null)
    {
        $this->sitemapIndex->addSitemap($location, $lastModified);
    }

    public function generateFile()
    {
        $this->sitemapIndex->write();
    }
}
