<?php

namespace App\Plugins\EPDocs\Storage;

use App\Plugins\EPDocs\Credentials;
use App\Plugins\EPDocs\CredentialsAware;
use App\Plugins\EPDocs\HttpClientAware;
use App\Plugins\EPDocs\TokenStorage;
use App\Plugins\EPDocs\TokenValidationAdapter;
use App\Plugins\EPDocs\TokenValidationAdapterAware;
use App\Plugins\EPDocs\Validator\JwtTokenValidator;
use GuzzleHttp\ClientInterface;
use function GuzzleHttp\json_decode;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token;

final class JwtTokenStorage implements TokenStorage, HttpClientAware, CredentialsAware, TokenValidationAdapterAware
{
    const PATH = '/authentication-token';

    const METHOD = 'POST';

    /**
     * The HTTP client.
     *GuzzleHttp\ClientInterface.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    private $httpClient;

    /**
     * The credentials.
     *
     * @var \App\Plugins\EPDocs\Credentials
     */
    private $credentials;

    /**
     * The token value.
     *
     * @var \Lcobucci\JWT\Parser
     */
    private $parser;

    /**
     * The token value.
     *
     * @var \Lcobucci\JWT\Token
     */
    private $token;

    /**
     * The JWT token validator.
     *
     * @var \App\Plugins\EPDocs\TokenValidationAdapter
     */
    private $validator;

    /**
     * The plain text token storage.
     */
    public function __construct(ClientInterface $httpClient, Credentials $credentials, TokenValidationAdapter $validator = null)
    {
        $this->parser = new Parser();
        $this->validator = $validator;
        $this->httpClient = $httpClient;
        $this->credentials = $credentials;
        if (null === $this->validator) {
            $this->validator = new JwtTokenValidator();
        }
    }

    /**
     * Returns the HTTP client.
     *GuzzleHttp\ClientInterface.
     *
     * @return \GuzzleHttp\ClientInterface
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * Returns the credentials.
     *
     * @return \App\Plugins\EPDocs\Credentials
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * Returns the validator.
     *
     * @return \App\Plugins\EPDocs\TokenValidationAdapter
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * Returns stored token.
     *
     * @return mixed
     */
    public function getToken()
    {
        if (null === $this->token) {
            $this->token = $this->requestToken();
        }

        return $this->token;
    }

    /**
     * Renews the token.
     */
    public function renewToken()
    {
        $this->token = $this->requestToken();
    }

    /**
     * Mades the request for the token.
     *
     * @return \Lcobucci\JWT\Token
     */
    private function requestToken()
    {
        $response = $this->httpClient->request(static::METHOD, static::PATH, [
            'json' => $this->credentials->toArray(),
        ]);
        $responseBody = json_decode($response->getBody()->getContents());

        return $this->parseToken($responseBody->token);
    }

    /**
     * Returns the token object from string value.
     *
     * @param string $tokenString
     *
     * @return \Lcobucci\JWT\Token
     */
    private function parseToken($tokenString)
    {
        return $this->parser->parse($tokenString);
    }
}
