<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Event;

use App\Messenger\Message\Command\SyncMatrixUser;
use App\Messenger\Message\Event\MatrixUserProfileRoomAddedEvent;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Starts the user's data synchronisation on the matrix server after profile room was created.
 *
 * @author Anton Zencenco
 */
final class SyncUserDataAfterProfileRoomWasCreated implements MessageSubscriberInterface
{
    /**
     * The command bus.
     */
    private MessageBusInterface $commandBus;

    /**
     * @param MessageBusInterface $commandBus the command bus
     */
    public function __construct(MessageBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * Sync user data when room created.
     */
    public function onProfileRoomCreated(MatrixUserProfileRoomAddedEvent $message)
    {
        $this->commandBus->dispatch(new SyncMatrixUser($message->getUserId(), true));
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield MatrixUserProfileRoomAddedEvent::class => ['bus' => 'event.bus', 'method' => 'onProfileRoomCreated'];
    }
}
