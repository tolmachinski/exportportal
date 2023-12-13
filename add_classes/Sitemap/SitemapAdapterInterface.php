<?php

namespace App\Sitemap;

interface SitemapAdapterInterface
{
    public function addItem(
        $location, 
        $lastModified = null, 
        $changeFrequency = null, 
        $priority = null
    );

    public function generateFiles();

    public function getSitemapUrls();
}
