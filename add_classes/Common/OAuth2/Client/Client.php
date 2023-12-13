<?php

declare(strict_types=1);

namespace App\Common\OAuth2\Client;

use App\Common\OAuth2\Client\Token\Storage\StorageInterface;
use App\Common\OAuth2\Exception\InvalidStateException;
use App\Common\OAuth2\Exception\MissingAuthorizationCodeException;
use App\Common\OAuth2\Exception\MissingCurrentrequestException;
use App\Common\OAuth2\Exception\MissingSessionException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Client implements ClientInterface
{
    /**
     * The flag that indicates that the current client is stateless.
     */
    private bool $stateless = false;

    /**
     * The grant type used by client to request access token.
     */
    private string $grantType;

    /**
     * The list of default authorization scopes. Used if none is provided.
     */
    private array $scopes;

    /**
     * The OAuth2 provider.
     */
    private AbstractProvider $provider;

    /**
     * The request stack for current.
     */
    private RequestStack $requestStack;

    /**
     * The token storage interface.
     */
    private StorageInterface $tokenStorage;

    /**
     * The current request.
     */
    private ?Request $currentRequest;

    /**
     * The current session.
     */
    private ?SessionInterface $session;

    public function __construct(
        AbstractProvider $provider,
        RequestStack $requestStack,
        ?StorageInterface $tokenStorage = null,
        ?string $grantType = self::DEFAULT_GRANT,
        ?array $scopes = [],
        bool $stateless = false
    ) {
        $this->scopes = $scopes;
        $this->provider = $provider;
        $this->grantType = $grantType ?? static::DEFAULT_GRANT;
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
        if ($stateless) {
            $this->setStateless();
        }
    }

    /**
     * Determines if client is stateless.
     */
    public function isStateless(): bool
    {
        return $this->stateless;
    }

    /**
     * Makes the client stateless.
     */
    public function setStateless(): void
    {
        $this->stateless = false;
    }

    /**
     * Resets the client.
     */
    public function reset(): void
    {
        $this->stateless = true;
    }

    /**
     * Get the list of default authorization scopes. Used if none is provided.
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * Get the grant type used by client to request access token.
     */
    public function getGrantType(): string
    {
        return $this->grantType;
    }

    /**
     * Get the OAuth2 provider.
     */
    public function getProvider(): AbstractProvider
    {
        return $this->provider;
    }

    /**
     * Get the token storage interface.
     */
    public function getTokenStorage(): ?StorageInterface
    {
        return $this->tokenStorage;
    }

    /**
     * Authorizes the user on the OAuth2 server.
     *
     * @param array $scopes  the authorization scopes
     * @param array $options extra options to pass to the Provider's getAuthorizationUrl() method
     */
    public function authorize(array $scopes = [], array $options = []): RedirectResponse
    {
        if (!empty($scopes)) {
            $options['scope'] = $scopes;
        } else {
            $options['scope'] = $this->getScopes();
        }
        // Always create URL before settin scope (if not stateless), because state is created only after the URL
        $url = $this->getProvider()->getAuthorizationUrl($options);
        // Set the state (unless we're stateless)
        if (!$this->isStateless()) {
            $this->getSession()->set(
                self::SESSION_STATE_KEY,
                $this->provider->getState()
            );
        }

        return $this->redirect($url);
    }

    /**
     * Returns the OAuth2 access token for given grant type. Must be called only after authorization.
     * If token storage is not used in client, it is effectevely equal to the @{@link static::getFreshAccessToken()}.
     *
     * @see static::getFreshAccessToken() Gets the stored token
     * @see static::getStoredAccessToken() Gets the fresh token
     *
     * @uses static::getFreshAccessToken()
     * @uses static::getStoredAccessToken()
     *
     * @throws InvalidStateException             if client when state is invalid and client in not stateless
     * @throws MissingAuthorizationCodeException when the authorization code is missing
     *
     * @return AccessToken
     */
    public function getAccessToken(array $options = []): AccessTokenInterface
    {
        // Get the token from the storage
        if (null !== $accessToken = $this->getStoredAccessToken()) {
            // If token has not expired - we will continue use it.
            if (!$accessToken->hasExpired()) {
                return $accessToken;
            }

            // Else if it has refresh token - we will refresh it on the place
            if (null !== $refreshToken = $accessToken->getRefreshToken()) {
                try {
                    return $this->refreshAccessToken($refreshToken, $options);
                } catch (IdentityProviderException $e) {
                    // If we get the IdentityProviderException here that means that the refresh token it totally dead.
                    // And we need to get new one.
                    // So pass on this one.
                }
            }
        }
        // But when the token is missing in the storage OR has no refresh token we will get the new one
        $accessToken = $this->getFreshAccessToken($options);

        try {
            // And store it.
            $this->getTokenStorage()->writeAccessToken($accessToken);
        } catch (\Throwable $e) {
            // Ooops
        }

        return $accessToken;
    }

    /**
     * Returns the stored  OAuth2 access token.
     *
     * @return AccessToken
     */
    public function getStoredAccessToken(): ?AccessTokenInterface
    {
        $tokenStorage = $this->getTokenStorage();
        if (null === $tokenStorage = $this->getTokenStorage()) {
            return null;
        }

        return $tokenStorage->readAccessToken();
    }

    /**
     * Returns the fresh OAuth2 access token for given grant type. Must be called only after authorization.
     *
     * @throws InvalidStateException             if client when state is invalid and client in not stateless
     * @throws MissingAuthorizationCodeException when the authorization code is missing
     *
     * @return AccessToken
     */
    public function getFreshAccessToken(array $options = []): AccessTokenInterface
    {
        $this->verifyState();
        if (null === ($code = $this->getCurrentRequest()->get('code') ?? null)) {
            throw new MissingAuthorizationCodeException('The "code" is not found in the current request.');
        }

        return $this->provider->getAccessToken($this->getGrantType(), array_merge($options, [
            'code' => $code,
        ]));
    }

    /**
     * Refresh the client's access token with given refresh token.
     *
     * @param string $refreshToken provided with previous access token
     * @param array  $options      additional options that should be passed to the getAccessToken() of the underlying provider
     *
     * @throws IdentityProviderException if token cannot be fetched
     */
    public function refreshAccessToken(string $refreshToken, array $options = []): AccessTokenInterface
    {
        $tokenStorage = $this->getTokenStorage();
        $accessToken = $this->provider->getAccessToken('refresh_token', array_merge(
            $options,
            ['refresh_token' => $refreshToken]
        ));
        if (null !== $tokenStorage) {
            try {
                $tokenStorage->writeAccessToken($accessToken);
            } catch (\Throwable $e) {
                // Ooops
            }
        }

        return $accessToken;
    }

    /**
     * Returns the resource owner information using provided access token.
     */
    public function getOwnerByToken(AccessTokenInterface $accessToken): ResourceOwnerInterface
    {
        return $this->provider->getResourceOwner(
            $accessToken
        );
    }

    /**
     * Shortcut to get the access token and user all at once. Must be used only in cases when you don't need the access
     * token, but only need resource owner information.
     *
     * @uses static::getOwnerByToken()
     */
    public function getOwner(): ResourceOwnerInterface
    {
        return $this->getOwnerByToken(
            $this->getAccessToken()
        );
    }

    /**
     * Verifies the state in the request, if client is not stateless.
     *
     * @throws InvalidStateException if client when state is invalid and client in not stateless
     */
    public function verifyState(): void
    {
        if ($this->isStateless()) {
            return;
        }

        $request = $this->getCurrentRequest();
        $currentState = $request->get('state');
        $expectedState = $this->getSession()->get(self::SESSION_STATE_KEY);
        if (!$currentState || ($currentState !== $expectedState)) {
            throw new InvalidStateException('Invalid OAuth2 state detected.');
        }
    }

    /**
     * Creates a RedirectResponse that will send the user to the provided URL.
     */
    protected function redirect(string $url, array $headers = []): RedirectResponse
    {
        return new RedirectResponse(
            $url,
            Response::HTTP_FOUND,
            $headers
        );
    }

    /**
     * Get the current request.
     */
    private function getCurrentRequest(): Request
    {
        if (!isset($this->currentRequest)) {
            $request = $this->requestStack->getCurrentRequest();
            if (!$request) {
                throw new MissingCurrentrequestException('The request stack has no requests and it is impossible to perform this action.');
            }

            $this->currentRequest = $request;
        }

        return $this->currentRequest;
    }

    /**
     * Get the current session.
     */
    private function getSession(): SessionInterface
    {
        if (!isset($this->session)) {
            $request = $this->getCurrentRequest();
            if (!$request->hasSession()) {
                throw new MissingSessionException('In order to use client state the session is requred. Set client to stateless to lift that requirement.');
            }

            $this->session = $request->getSession();
        }

        return $this->session;
    }
}
