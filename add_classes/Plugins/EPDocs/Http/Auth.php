<?php

namespace App\Plugins\EPDocs\Http;

use App\Plugins\EPDocs\AuthenticationClient;
use App\Plugins\EPDocs\Storage\NullTokenStorage;
use App\Plugins\EPDocs\TokenStorage;
use App\Plugins\EPDocs\TokenValidationAdapterAware;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\RequestInterface;

final class Auth implements AuthenticationClient
{
    /**
     * The HTTP client.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    private $httpClient;

    /**
     * The authentication type.
     *
     * @var \App\Plugins\EPDocs\Http\Authentication
     */
    private $authenticationType;

    /**
     * The token validator.
     *
     * @var \App\Plugins\EPDocs\TokenStorage
     */
    private $tokenStorage;

    public function __construct(
        ClientInterface $httpClient,
        Authentication $authenticationType,
        TokenStorage $tokenStorage = null
    ) {
        $this->httpClient = $httpClient;
        $this->authenticationType = $authenticationType;
        $this->tokenStorage = $tokenStorage;
        if (null === $this->tokenStorage) {
            $this->tokenStorage = new NullTokenStorage();
        }
    }

    /**
     * Performs authentication of the request.
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function doAuthenticate(RequestInterface $request)
    {
        $token = $this->tokenStorage->getToken();
        $tokenStorage = $this->tokenStorage;
        if (
            $tokenStorage instanceof TokenValidationAdapterAware
            && !$tokenStorage->getValidator()->validateToken($token)
        ) {
            $tokenStorage->renewToken();
            $token = $this->tokenStorage->getToken();
        }

        return $this->authenticationType->withToken($token)->authenticate($request);
    }
}
