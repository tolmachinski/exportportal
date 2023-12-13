<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Event;

use App\Messenger\Message\Command\CreateMatrixUser;
use App\Messenger\Message\Event\Lifecycle as LifecycleEvent;
use App\Messenger\Message\Event\UserHasRegisteredEvent;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Creates user's account on the matrix server ater user registered.
 *
 * @author Anton Zencenco
 */
final class ExportUserToMatrixAfterRegistration implements MessageSubscriberInterface
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
     * ACtivate sync when room created.
     */
    public function onUserRegistered(LifecycleEvent\UserHasRegisteredEvent $message)
    {
        $this->commandBus->dispatch(new CreateMatrixUser($message->getUserId()));
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield LifecycleEvent\UserHasRegisteredEvent::class => ['bus' => 'event.bus', 'method' => 'onUserRegistered'];
        yield UserHasRegisteredEvent::class                => ['bus' => 'event.bus', 'method' => 'onUserRegistered'];
    }
}
