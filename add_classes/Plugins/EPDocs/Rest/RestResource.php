<?php

declare(strict_types=1);

namespace App\Plugins\EPDocs\Rest;

use App\Plugins\EPDocs\AuthenticationClient;
use App\Plugins\EPDocs\AuthenticationClientAware;
use App\Plugins\EPDocs\AuthenticationException;
use App\Plugins\EPDocs\BadRequestException;
use App\Plugins\EPDocs\Client;
use App\Plugins\EPDocs\HttpClientAware;
use App\Plugins\EPDocs\NotFoundException;
use App\Plugins\EPDocs\Resource;
use App\Plugins\EPDocs\ServerException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException as HttpClientException;
use GuzzleHttp\Exception\ServerException as HttpServerException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

use function GuzzleHttp\json_decode;

abstract class RestResource implements Resource, HttpClientAware, AuthenticationClientAware
{
    /**
     * The API client.
     *
     * @var \App\Plugins\EPDocs\Client
     */
    private $apiClient;

    /**
     * The HTTP client.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    private $httpClient;

    /**
     * The authenticator client.
     *
     * @var \App\Plugins\EPDocs\AuthenticationClient
     */
    private $authenticationClient;

    public function __construct(Client $client, ClientInterface $httpClient, AuthenticationClient $authenticationClient)
    {
        $this->apiClient = $client;
        $this->httpClient = $httpClient;
        $this->authenticationClient = $authenticationClient;
    }

    /**
     * Returns the API client.
     *
     * @return \App\Plugins\EPDocs\Client
     */
    public function getApiClient()
    {
        return $this->apiClient;
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
     * Returns the autheticator.
     *
     * @return \App\Plugins\EPDocs\AuthenticationClient
     */
    public function getAuthenticationClient()
    {
        return $this->authenticationClient;
    }

    /**
     * Sends request.
     *
     * @param string $method
     * @param string $uri
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function sendRequest($method, $uri, array $options = [])
    {
        try {
            return $this->getHttpClient()->send(
                $this->getAuthenticationClient()->doAuthenticate(new Request($method, $uri)),
                $options
            );
        } catch (HttpClientException $exception) {
            if (400 === $exception->getCode()) {
                throw new BadRequestException('The request failed - request body is malformed or not valid', 400, $exception);
            }
            if (401 === $exception->getCode()) {
                throw new AuthenticationException('The authentication failed', 401, $exception);
            }
            if (404 === $exception->getCode()) {
                throw new NotFoundException('The resource is not found', 404, $exception);
            }

            // Roll it...
            throw $exception;
        } catch (HttpServerException $exception) {
            throw new ServerException('The server responded with error', $exception->getCode(), $exception);
        }
    }

    /**
     * Returns parsed request body.
     *
     * @return array
     */
    protected function getParsedResponseBody(ResponseInterface $response)
    {
        return json_decode($response->getBody()->getContents(), true);
    }
}
