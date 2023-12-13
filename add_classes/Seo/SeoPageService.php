<?php

declare(strict_types=1);

namespace App\Seo;

use InvalidArgumentException;
use LogicException;

final class SeoPageService
{
    private $meta = [
        'canonicalUrl' => null,
    ];

    /**
     * Get current URL as canonical value.
     */
    public function getCanonicalUrl(): string
    {
        if (!isset($this->meta['canonicalUrl'])) {
            throw new LogicException('The canonical URL must defined first.');
        }

        return (string) $this->meta['canonicalUrl'];
    }

    /**
     * Adds new URL to default URL of getCanonicalUrl().
     *
     * @param string $url - URL for changing default URl
     */
    public function setCanonicalUrl(string $url): self
    {
        if (!\filter_var($url, \FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('The $url argument must be a valid URL.');
        }
        $this->meta['canonicalUrl'] = $url;

        return $this;
    }
}
