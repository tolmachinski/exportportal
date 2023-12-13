<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Event;

use App\Messenger\Message\Command\CreateMatrixPorfileRoom;
use App\Messenger\Message\Command\CreateMatrixServerNoticesRoom;
use App\Messenger\Message\Command\Matrix\CreateCargoRoom;
use App\Messenger\Message\Event\MatrixUserAddedEvent;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Creates the service rooms (profile, server notices) after the user's account was created on matrix server.
 *
 * @author Anton Zencenco
 */
final class CreateServiceRoomsAfterMatrixUserAdded implements MessageSubscriberInterface
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
     * {@inheritDoc}
     */
    public function __invoke(MatrixUserAddedEvent $message): void
    {
        $this->commandBus->dispatch(new CreateMatrixServerNoticesRoom($message->getUserId()));
        $this->commandBus->dispatch(new CreateCargoRoom($message->getUserId()));
        $this->commandBus->dispatch(new CreateMatrixPorfileRoom($message->getUserId()));
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield MatrixUserAddedEvent::class => ['bus' => 'event.bus'];
    }
}
