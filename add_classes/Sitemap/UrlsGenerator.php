<?php

namespace App\Sitemap;

class UrlsGenerator implements UrlSourceInterface
{
    private $urlsCollection;

    public function __construct(\Traversable $sourceUrls)
    {
        $this->urlsCollection = $sourceUrls;
    }

    public function getUrls() 
    {
        foreach ($this->urlsCollection as $urlData) {
            yield $urlData;
        }
    }
}
