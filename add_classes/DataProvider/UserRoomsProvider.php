<?php

declare(strict_types=1);

namespace App\DataProvider;

use App\Common\Database\Model;
use ExportPortal\Bridge\Matrix\DataProvider\UserRoomsProviderInterface;
use ExportPortal\Bridge\Matrix\Notifier\Recipient\RoomType;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @author Anton Zencenco
 */
final class UserRoomsProvider implements UserRoomsProviderInterface
{
    /**
     * The matrix users repository.
     */
    private Model $matrixUsersRepository;

    /**
     * The cache instance.
     */
    private CacheInterface $cache;

    /**
     * @param Model $matrixUsersRepository the matrix users repository
     */
    public function __construct(Model $matrixUsersRepository, CacheInterface $cache)
    {
        $this->cache = $cache;
        $this->matrixUsersRepository = $matrixUsersRepository;
    }

    /**
     * {@inheritDoc}
     *
     * @param int $userId
     */
    public function getRoomId($userId, RoomType $roomType): ?string
    {
        $user = $this->cache->get((string) $userId, fn () => $this->matrixUsersRepository->findOneBy(['scopes' => ['user' => $userId]]));
        switch ($roomType) {
            case RoomType::CARGO(): return $user['cargo_room_id'] ?? null;
            case RoomType::NOTICE(): return $user['server_notices_room_id'] ?? null;
            case RoomType::PROFILE(): return $user['profile_room_id'] ?? null;

            default: return null;
        }
    }
}
