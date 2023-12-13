<?php

declare(strict_types=1);

namespace App\OAuth2\Client\Provider;

use App\Common\OAuth2\Client\Provider\ResponseTypeAwareProviderInterface;
use App\Common\OAuth2\Client\Provider\ResponseTypeAwareTrait;
use App\OAuth2\Client\Provider\ResourceOwner\DocuSignUser;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class DocuSign extends AbstractProvider implements ResponseTypeAwareProviderInterface
{
    use BearerAuthorizationTrait;
    use ResponseTypeAwareTrait;

    public const ENDPOINT_ACCESS_TOKEN = '/oauth/token';
    public const ENDPOINT_AUTHORIZTION = '/oauth/auth?prompt=login';
    public const ENDPOINT_AUTHORIZTION_SILENT = '/oauth/auth';
    public const ENDPOINT_RESOURCE_OWNER_DETAILS = '/oauth/userinfo';

    public const SCOPES_SEPARATOR = ' ';

    public const SCOPE_EXTENDED = 'extended';
    public const SCOPE_SIGNATURE = 'signature';
    public const SCOPE_IMPERSONATION = 'impersonation';
    public const SCOPES_DEFAULT = [
        self::SCOPE_SIGNATURE,
    ];

    /**
     * The URL of the optimization service.
     */
    protected ?string $authorizationServer = null;

    /**
     * The flag that enables and disables silent authentication.
     */
    protected bool $allowSilentAuth = true;

    /**
     * The target account ID. Default is NULL which means the default account will be used.
     */
    protected ?string $targetAccountId = null;

    /**
     * {@inheritdoc}
     */
    public function getBaseAuthorizationUrl()
    {
        if ($this->allowSilentAuth) {
            return $this->getUrl(static::ENDPOINT_AUTHORIZTION_SILENT);
        }

        return $this->getUrl(static::ENDPOINT_AUTHORIZTION);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->getUrl(static::ENDPOINT_ACCESS_TOKEN);
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->getUrl(static::ENDPOINT_RESOURCE_OWNER_DETAILS);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultScopes()
    {
        return static::SCOPES_DEFAULT;
    }

    /**
     * Returns a full url for the given path, with the appropriate docusign backend.
     *
     * It can be used in combination of getRequest and getResponse methods
     * to make further calls to docusign endpoint using the given token.
     *
     * @see Docusign::getRequest
     * @see Docusign::getResponse
     */
    public function getUrl(string $path): string
    {
        return sprintf(
            '%s/%s',
            $this->getAuthServer(),
            \ltrim($path, '/')
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getScopeSeparator()
    {
        return static::SCOPES_SEPARATOR;
    }

    /**
     * Returns the DocuSign authorization server url.
     *
     * @throws \Exception
     */
    protected function getAuthServer(): string
    {
        $url = $this->authorizationServer;
        if (null === $url) {
            throw new \Exception('The "authorizationServer" is not set.');
        }

        return $url;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            throw new IdentityProviderException(
                $response->getReasonPhrase(),
                $response->getStatusCode(),
                $response
            );
        }

        if (isset($data['error'])) {
            throw new IdentityProviderException(
                $data['error'],
                0,
                $response
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new DocuSignUser($response, $this->targetAccountId);
    }
}
