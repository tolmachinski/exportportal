<?php

declare(strict_types=1);

namespace App\Plugins\EPDocs;

use App\Plugins\EPDocs\Rest\Objects\User as UserObject;
use App\Plugins\EPDocs\Rest\Resources\User as UserResource;

trait UserAwareTrait
{
    use ApiAwareTrait;

    /**
     * The users resources.
     */
    private UserResource $usersResource;

    /**
     * The cache key.
     */
    private ?string $cacheKey;

    /**
     * The cache TTL.
     */
    private ?int $cacheTtl;

    /**
     * Get the cache key.
     */
    public function getUserCacheKeyPrefix(): ?string
    {
        return $this->cacheKey;
    }

    /**
     * Get the cache TTL.
     */
    public function getUserCacheTtl(): ?int
    {
        return $this->cacheTtl;
    }

    /**
     * Returns the API context for given user.
     *
     * @param int|string $userId
     *
     * @deprecated in favor of Util
     */
    private function getUserApiContext($userId): array
    {
        return Util::createContext($userId, $this->getApiClient()->getConfiguration()->getHttpOrigin());
    }

    /**
     * Returns generic admin user object.
     *
     * @param int|string $userId
     */
    private function getUserFromApi($userId): UserObject
    {
        $user = $this->getUserFromCache($userId);
        if (null === $user || null === $user->getId()) {
            $user = $this->usersResource->findUserIfNotCreate($this->getUserApiContext($userId));
            $this->cachePool->set(
                "{$this->getUserCacheKeyPrefix()}-" . (is_string($userId) ? \base64_encode($userId) : $userId),
                $user->toArray(),
                $this->getUserCacheTtl() ?? 1
            );
        }

        return $user;
    }

    /**
     * Returns the list if users from API.
     *
     * @return iterable<UserObject>
     */
    private function getUsersFromApi(array $usersIds): iterable
    {
        foreach ($usersIds as $userId) {
            yield $userId => $this->getUserFromApi($userId);
        }
    }

    /**
     * Returns user from cache.
     *
     * @param int|string $userId
     */
    private function getUserFromCache($userId): ?UserObject
    {
        $cachePool = $this->getCachePool();
        $cacheKeyPrefix = $this->getUserCacheKeyPrefix();
        if (null !== $cachePool && null !== $cacheKeyPrefix) {
            return null;
        }

        $cacheKey = "{$cacheKeyPrefix}-" . (is_string($userId) ? \base64_encode($userId) : $userId);
        $cachedUser = $this->cachePool->get($cacheKey);
        if (null !== $cachedUser) {
            $cachedUser = UserObject::fromArray((array) $cachedUser);
        }

        return $cachedUser;
    }
}
