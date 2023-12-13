<?php

declare(strict_types=1);

namespace App\Plugins\EPDocs\Rest;

use App\Plugins\EPDocs\ApiClient;
use App\Plugins\EPDocs\AuthenticationClient;
use App\Plugins\EPDocs\AuthenticationClientAware;
use App\Plugins\EPDocs\Configuration;
use App\Plugins\EPDocs\HttpClientAware;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class RestClient extends ApiClient implements HttpClientAware, AuthenticationClientAware
{
    use LoggerAwareTrait;

    /**
     * The HTTP client.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    private $httpClient;

    /**
     * The client configurations.
     *
     * @var Configuration
     */
    private $configuration;

    /**
     * The authenticator client.
     *
     * @var \App\Plugins\EPDocs\AuthenticationClient
     */
    private $authenticationClient;

    public function __construct(ClientInterface $httpClient, AuthenticationClient $authenticationClient, Configuration $configuration, ?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        $this->httpClient = $httpClient;
        $this->configuration = $configuration;
        $this->authenticationClient = $authenticationClient;
    }

    /**
     * Returns the API logger.
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Returns the HTTP client.
     *
     * @return \GuzzleHttp\ClientInterface
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * Get the client configurations.
     *
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Returns the autheticator.
     *
     * @return \App\Plugins\EPDocs\AuthenticationClient
     */
    public function getAuthenticationClient()
    {
        return $this->authenticationClient;
    }

    /**
     * Checks if resource name is accepted.
     *
     * @param string $resourceName
     *
     * @return bool
     */
    protected function isAcceptedResourceName($resourceName)
    {
        return is_a($resourceName, RestResource::class, true);
    }

    /**
     * {@inheritdoc}
     */
    protected function createResource($resourceName)
    {
        return new $resourceName($this, $this->httpClient, $this->authenticationClient);
    }
}
