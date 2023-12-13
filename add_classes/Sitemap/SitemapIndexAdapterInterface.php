<?php

namespace App\Sitemap;

interface SitemapIndexAdapterInterface
{
    public function addSitemap($location, $lastModified = null);

    public function generateFile();
}
