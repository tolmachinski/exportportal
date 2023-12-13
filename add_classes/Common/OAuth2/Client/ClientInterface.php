<?php

declare(strict_types=1);

namespace App\Common\OAuth2\Client;

use App\Common\OAuth2\Client\Token\Storage\StorageInterface;
use App\Common\OAuth2\Exception\InvalidStateException;
use App\Common\OAuth2\Exception\MissingAuthorizationCodeException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

interface ClientInterface
{
    public const DEFAULT_GRANT = 'authorization_code';

    public const SESSION_STATE_KEY = 'app_session_oauth2_client_key';

    /**
     * Determines if client is stateless.
     */
    public function isStateless(): bool;

    /**
     * Makes the client stateless.
     */
    public function setStateless(): void;

    /**
     * Resets the client.
     */
    public function reset(): void;

    /**
     * Get the list of default authorization scopes. Used if none is provided.
     */
    public function getScopes(): array;

    /**
     * Get the grant type used by client to request access token.
     */
    public function getGrantType(): string;

    /**
     * Get the OAuth2 provider.
     */
    public function getProvider(): AbstractProvider;

    /**
     * Get the token storage interface.
     */
    public function getTokenStorage(): ?StorageInterface;

    /**
     * Authorizes the user on the OAuth2 server.
     *
     * @param array $scopes  the authorization scopes
     * @param array $options extra options to pass to the Provider's getAuthorizationUrl() method
     */
    public function authorize(array $scopes = [], array $options = []): RedirectResponse;

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
    public function getAccessToken(array $options = []): AccessTokenInterface;

    /**
     * Returns the stored  OAuth2 access token.
     *
     * @return AccessToken
     */
    public function getStoredAccessToken(): ?AccessTokenInterface;

    /**
     * Returns the fresh OAuth2 access token for given grant type. Must be called only after authorization.
     *
     * @throws InvalidStateException             if client when state is invalid and client in not stateless
     * @throws MissingAuthorizationCodeException when the authorization code is missing
     *
     * @return AccessToken
     */
    public function getFreshAccessToken(array $options = []): AccessTokenInterface;

    /**
     * Refresh the client's access token with given refresh token.
     *
     * @param string $refreshToken provided with previous access token
     * @param array  $options      additional options that should be passed to the getAccessToken() of the underlying provider
     *
     * @throws IdentityProviderException if token cannot be fetched
     */
    public function refreshAccessToken(string $refreshToken, array $options = []): AccessTokenInterface;

    /**
     * Returns the resource owner information using provided access token.
     */
    public function getOwnerByToken(AccessTokenInterface $accessToken): ResourceOwnerInterface;

    /**
     * Shortcut to get the access token and user all at once. Must be used only in cases when you don't need the access
     * token, but only need resource owner information.
     *
     * @uses static::getOwnerByToken()
     */
    public function getOwner(): ResourceOwnerInterface;

    /**
     * Verifies the state in the request, if client is not stateless.
     *
     * @throws InvalidStateException if client when state is invalid and client in not stateless
     */
    public function verifyState(): void;
}
