<?php

namespace App\Common\Traits;

use App\Plugins\EPDocs\Credentials\JwtCredentials;
use App\Plugins\EPDocs\Http\Auth;
use App\Plugins\EPDocs\Http\Authentication\Bearer;
use App\Plugins\EPDocs\Rest\Objects\User as UserObject;
use App\Plugins\EPDocs\Rest\Resources\User as UserResource;
use App\Plugins\EPDocs\Rest\RestClient;
use App\Plugins\EPDocs\Storage\JwtTokenStorage;
use App\Plugins\EPDocs\Util;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Storage\Psr16CacheStorage;
use Kevinrob\GuzzleCache\Strategy\PublicCacheStrategy;
use Psr\SimpleCache\CacheInterface;

trait DocumentsApiAwareTrait
{
    /**
     * Returns the EPDocs API client.
     *
     * @return RestClient
     */
    private function getApiClient($enableCache = true)
    {
        $stack = HandlerStack::create();
        if ($enableCache) {
            $stack->push(
                new CacheMiddleware(
                    new PublicCacheStrategy(
                        new Psr16CacheStorage(
                            library('fastcache')->pool('epdocs')
                        )
                    )
                )
            );
        }

        $client = new Client(array('base_uri' => config('env.EP_DOCS_HOST', 'http://localhost'), 'handler' => $stack));
        $storage = new JwtTokenStorage($client, new JwtCredentials(
            config('env.EP_DOCS_API_USERNAME'),
            config('env.EP_DOCS_API_SECRET')
        ));
        $auth = new Auth($client, new Bearer(), $storage);
        $configs = (new \App\Plugins\EPDocs\Configuration())
            ->setHttpOrigin(config('env.EP_DOCS_REFERRER'))
            ->setDefaultUserId(config('env.EP_DOCS_ADMIN_SALT'))
        ;

        return new RestClient($client, $auth, $configs);
    }

    /**
     * Returns generic admin user object.
     *
     * @return UserObject
     */
    private function getGenericUser(UserResource $users)
    {
        /** @var CacheInterface $pool */
        $pool = $this->getCachePool();
        /** @var UserObject $genericUser */
        if (
            !$pool->has('generic_user')
            || null === ($genericUser = UserObject::fromArray((array) $pool->get('generic_user')))
            || null === $genericUser->getId()
        ) {
            $genericUser = $users->findUserIfNotCreate($this->getUserApiContext(config('env.EP_DOCS_ADMIN_SALT'))); // Create or get generic user
            $pool->set('generic_user', $genericUser->toArray(), 12 * 60 * 60);
        }

        return $genericUser;
    }

    /**
     * Returns the user objet cached in outer cache.
     * Can be used in cases when inner cache not working.
     *
     * @param UserResource $users
     * @param int          $userId
     *
     * @return UserObject
     */
    private function getCachedUser(UserResource $users, $userId)
    {
        /** @var CacheInterface $pool */
        $pool = $this->getCachePool();
        /** @var UserObject $user */
        if (
            !$pool->has("user-{$userId}")
            || null === ($user = UserObject::fromArray((array) $pool->get("user-{$userId}")))
            || null === $user->getId()
        ) {
            $user = $users->findUserIfNotCreate($this->getUserApiContext($userId)); // Create or get manager
            $pool->set("user-{$userId}", $user->toArray(), 12 * 60 * 60);
        }

        return $user;
    }

    /**
     * Returns the API context for given user.
     *
     * @param int $userId
     *
     * @return array
     *
     * @deprecated in favor of the Util::createContext()
     */
    private function getUserApiContext($userId)
    {
        return Util::createContext($userId, config('env.EP_DOCS_REFERRER', 'http://localhost'));
    }

    /**
     * Returns the cache pool.
     *
     * @return \Psr\SimpleCache\CacheInterface
     */
    private function getCachePool()
    {
        return library('fastcache')->pool('epdocs');
    }
}
