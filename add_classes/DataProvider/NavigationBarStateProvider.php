<?php

declare(strict_types=1);

namespace App\DataProvider;

use Symfony\Contracts\Cache\CacheInterface;
use TinyMVC_Library_Session as LegacySessionHandler;
use User_System_Messages_Model;

final class NavigationBarStateProvider
{
    /**
     * The instance of the cache adapter.
     */
    private CacheInterface $cacheAdapter;

    /**
     * The legacy session handler.
     */
    private LegacySessionHandler $sessionHandler;

    /**
     * The imessages repository instance.
     */
    private User_System_Messages_Model $messagesRepository;

    /**
     * @param User_System_Messages_Model $messagesRepository the imessages repository instance
     * @param LegacySessionHandler       $sessionHandler     the legacy session handler
     * @param CacheInterface             $cacheAdapter       the instance of the cache adapter
     */
    public function __construct(
        User_System_Messages_Model $messagesRepository,
        LegacySessionHandler $sessionHandler,
        CacheInterface $cacheAdapter
    ) {
        $this->cacheAdapter = $cacheAdapter;
        $this->sessionHandler = $sessionHandler;
        $this->messagesRepository = $messagesRepository;
    }

    /**
     * Returns the state for navigation bar in the views.
     *
     * @return array{count_notifications:?array{count_new:int,count_warning:int,count_all:int},complete_profile:?array{total_complete:?bool},lastNotificationId:int}
     */
    public function getState(?int $userId): array
    {
        if (!logged_in()) {
            return [];
        }

        return $this->cacheAdapter->get("notification_counts.{$userId}", fn () => [
            'completeProfile'    => $this->sessionHandler->get('completeProfile'),
            'countNotifications' => \array_map(fn ($v) => (int) $v, $this->messagesRepository->counterUserNotifications($userId)),
            'lastNotificationId' => (int) $this->messagesRepository->findOneBy([
                'conditions' => [
                    'userId'        => $userId,
                    'calendarOnly'  => 0,
                ],
                'order' => [
                    "{$this->messagesRepository->qualifyColumn('id_um')}" => 'DESC',
                ],
            ])['id_um'] ?: 0,
        ]);
    }
}
