<?php

declare(strict_types=1);

use App\Common\Encryption\MasterKeyAwareTrait;
use App\Common\Exceptions\AccessDeniedException;
use App\Common\Exceptions\NotFoundException;
use App\Common\OAuth2\Client\Provider\ResponseTypeAwareProviderInterface;
use App\Common\OAuth2\Exception\InvalidStateException;
use App\Common\OAuth2\Exception\MissingAuthorizationCodeException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use ParagonIE\Halite\Asymmetric\Crypto;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller Oauth2.
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Oauth2_Controller extends TinyMVC_Controller
{
    use MasterKeyAwareTrait;

    private const SESSION_VERIFICATION_KEY = 'app_session_oauth2_verification_key';

    /**
     * Authorizes oAuth2 request.
     */
    public function authorize(): RedirectResponse
    {
        try {
            $request = request();
            /** @var TinyMVC_Library_Oauth2 $oAuth2 */
            $oAuth2 = library(TinyMVC_Library_Oauth2::class);
            $client = $oAuth2->client($request->get('type') ?? '');
        } catch (NotFoundException $e) {
            redirectWithMessage('/404', throwableToMessage($e, translate('systmess_error_invalid_data', null, true)));
        } catch (Exception $e) {
            redirectWithMessage('/403', throwableToMessage($e, translate('systmess_error_permission_not_granted', null, true)));
        }

        return $client->authorize();
    }

    /**
     * Processes the authorization.
     */
    public function process(): void
    {
        try {
            $request = request();
            /** @var TinyMVC_Library_Oauth2 $oAuth2 */
            $oAuth2 = library(TinyMVC_Library_Oauth2::class);
            $oAuth2Client = $oAuth2->client($providerType = $request->get('type') ?? '');

            try {
                $accessToken = $oAuth2Client->getAccessToken();
            } catch (InvalidStateException | MissingAuthorizationCodeException $e) {
                $provider = $oAuth2Client->getProvider();
                if ($provider instanceof ResponseTypeAwareProviderInterface && 'token' === $provider->getDefaultResponseType()) {
                    $request->getSession()->set(
                        static::SESSION_VERIFICATION_KEY,
                        $verificationKey = base64_encode(\random_bytes(128))
                    );

                    views('admin/oauth2/token_view', arrayCamelizeAssocKeys([
                        'type'         => $providerType,
                        'hmac_key'     => Crypto::sign($verificationKey, $this->getMasterKey()->getSecretKey()),
                        'redirect_uri' => getUrlForGroup('/oauth2/store_implicit_token'),
                        'access_token' => $accessToken,
                    ]));

                    return;
                }

                throw $e;
            }
        } catch (NotFoundException $e) {
            redirectWithMessage('/404', throwableToMessage($e, translate('systmess_error_invalid_data', null, true)));
        } catch (IdentityProviderException | InvalidStateException | Exception $e) {
            redirectWithMessage('/403', throwableToMessage($e, translate('systmess_error_permission_not_granted', null, true)));
        }

        views('admin/oauth2/index_view', ['access_token' => $accessToken]);
    }

    /**
     * Stores implicit token.
     */
    public function store_implicit_token(): void
    {
        $request = request();
        /** @var TinyMVC_Library_Oauth2 $oAuth2 */
        $oAuth2 = library(TinyMVC_Library_Oauth2::class);
        $oAuth2Client = $oAuth2->client($request->get('type') ?? '');
        $accessToken = new AccessToken(with($request->query->all(), function (array $tokenData) {
            unset($tokenData['type'], $tokenData['state'], $tokenData['verification_code']);

            return $tokenData;
        }));

        try {
            $verificationKey = $request->query->get('verification_code') ?? null;
            $verificationCode = $request->getSession()->get(static::SESSION_VERIFICATION_KEY);
            if (!Crypto::verify($verificationCode, $this->getMasterKey()->getPublicKey(), $verificationKey)) {
                throw new AccessDeniedException("The request didn't passed the MAC verification");
            }

            $oAuth2Client->verifyState();
            $oAuth2Client->getTokenStorage()->writeAccessToken($accessToken);
        } catch (AccessDeniedException | InvalidStateException $e) {
            redirectWithMessage('/403', throwableToMessage($e, translate('systmess_error_permission_not_granted', null, true)));
        } catch (\Throwable $e) {
            redirectWithMessage('/404', throwableToMessage($e, translate('systmess_error_invalid_data', null, true)));
        }

        views('admin/oauth2/index_view');
    }
}

// End of file oauth2.php
// Location: /tinymvc/myapp/controllers/oauth2.php
