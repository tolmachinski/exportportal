<?php

declare(strict_types=1);

namespace App\Plugins\EPDocs;

use App\Plugins\EPDocs\Rest\RestClient;
use Psr\SimpleCache\CacheInterface;

trait ApiAwareTrait
{
    /**
     * The rest API client.
     */
    private RestClient $apiClient;

    /**
     * The cache pool.
     */
    private ?CacheInterface $cachePool;

    /**
     * The REST API request origin.
     *
     * @deprecated
     */
    private string $apiRequestOrigin;

    /**
     * Get the rest API client.
     */
    public function getApiClient(): RestClient
    {
        return $this->apiClient;
    }

    /**
     * Get the cache pool.
     *
     * @deprecated
     */
    public function getCachePool(): ?CacheInterface
    {
        return $this->cachePool;
    }

    /**
     * Get the REST API request origin.
     *
     * @deprecated
     */
    public function getApiRequestOrigin(): string
    {
        return $this->apiRequestOrigin;
    }
}
