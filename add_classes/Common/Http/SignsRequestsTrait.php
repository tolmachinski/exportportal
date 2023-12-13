<?php

namespace App\Common\Http;

use App\Common\Encryption\MasterKeyAwareTrait;
use App\Common\Exceptions\AccessDeniedException;
use ParagonIE\Halite\Asymmetric\Crypto;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;

trait SignsRequestsTrait
{
    use MasterKeyAwareTrait;

    /**
     * Signs the redirect URL.
     */
    private function signRedirectResponse(Request $request, RedirectResponse $response, int $ttl = -1): RedirectResponse
    {
        //region Request & session
        if (!$request->hasSession()) {
            $request->setSession(new Session(new PhpBridgeSessionStorage()));
        }
        //endregion Request & session

        $now = \time();
        $url = $response->getTargetUrl();
        $state = \base64_encode(\random_bytes(16));
        $ciphertext = \base64_encode(\random_bytes(128));
        $separator = null === \parse_url($url, PHP_URL_QUERY) ? '?' : '&';
        $request->getSession()->set(\constant('static::SIGNED_URL_CIPHERTEXT_SESSION_KEY'), \tap(
            $request->getSession()->get(\constant('static::SIGNED_URL_CIPHERTEXT_SESSION_KEY')) ?? new ParameterBag(),
            function (ParameterBag $stateBag) use ($ciphertext, $state, $ttl, $now) {
                // Filter expired values
                foreach ($stateBag as $key => list('expires' => $expires)) {
                    if (null !== $expires && $expires > 0 && $now > $expires) {
                        $stateBag->remove($key);
                    }
                }

                // Add new value
                $stateBag->set($state, [
                    'text'    => $ciphertext,
                    'expires' => -1 !== $ttl ? $now + \max(0, $ttl) : -1,
                ]);
            }
        ));

        return new RedirectResponse(
            \sprintf('%s%s%s', $url, $separator, \http_build_query([
                \constant('static::SIGNED_URL_STATE_KEY')     => $state,
                \constant('static::SIGNED_URL_MAC_QUERY_KEY') => Crypto::sign($ciphertext, $this->getMasterKey()->getSecretKey()),
            ])),
            $response->getStatusCode(),
            $response->headers->all()
        );
    }

    /**
     * Verifies if MAC-signed request is valid.
     *
     * @throws AccessDeniedException if request didn't pass the verification
     */
    private function verifySignedRequest(Request $request): void
    {
        //region Request & session
        if (!$request->hasSession()) {
            $request->setSession(new Session(new PhpBridgeSessionStorage()));
        }
        //endregion Request & session

        $now = \time();
        $state = $request->get(\constant('static::SIGNED_URL_STATE_KEY')) ?? '';
        $macKey = $request->get(\constant('static::SIGNED_URL_MAC_QUERY_KEY')) ?? '';
        /** @var ParameterBag $stateBag */
        $stateBag = $request->getSession()->get(\constant('static::SIGNED_URL_CIPHERTEXT_SESSION_KEY')) ?? new ParameterBag();
        list('text' => $ciphertext, 'expires' => $expires) = \tap(
            $stateBag->get($state) ?? [],
            function () use ($stateBag, $state, $now) {
                $stateBag->remove($state);
                // Filter expired values
                foreach ($stateBag as $key => list('expires' => $expires)) {
                    if (null !== $expires && $expires > 0 && $now > $expires) {
                        $stateBag->remove($key);
                    }
                }
            }
        );
        if (empty($state) || empty($macKey) || empty($ciphertext)) {
            throw new AccessDeniedException('The request is not signed with MAC.');
        }
        if (null !== $expires && $expires > 0 && $now > $expires) {
            throw new AccessDeniedException('The MAC code has been expired.');
        }

        try {
            // Verify access usign MAC-signing
            $isValid = Crypto::verify($ciphertext, $this->getMasterKey()->getPublicKey(), $macKey);
        } catch (\Throwable $e) {
            $isValid = false;
        } finally {
            if (false === ($isValid ?? false)) {
                throw new AccessDeniedException("The request didn't pass the MAC verification.", 0, $e ?? null);
            }
        }
    }
}
