<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Event;

use App\Messenger\Message\Event\MatrixChatRoomAddedEvent;
use App\Services\ChatBindingService;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Creates the reference between the matrix room and resource after matrix room was created.
 *
 * @author Anton Zencenco
 */
final class BindResourceWhenMatrixChatRoomAdded implements MessageSubscriberInterface
{
    /**
     * The chat binding service.
     */
    private ChatBindingService $bindingService;

    /**
     * @param ChatBindingService $bindingService the chat binding service
     */
    public function __construct(ChatBindingService $bindingService)
    {
        $this->bindingService = $bindingService;
    }

    /**
     * Bind resource when chat room is created.
     */
    public function onChatRoomCreated(MatrixChatRoomAddedEvent $message)
    {
        // Leave if there is no room options or there is not resources.
        if (
            null === ($roomOptions = $message->getOptions())
            || null === ($resourceOptions = $roomOptions->getResource())
            || null === $resourceOptions->getType()
        ) {
            return;
        }

        $this->bindingService->bindResourceToRoom(
            $resourceOptions,
            $message->getRoomId(),
            $roomOptions->getSenderId(),
            $roomOptions->getRecipientId()
        );
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield MatrixChatRoomAddedEvent::class => ['bus' => 'event.bus', 'method' => 'onChatRoomCreated'];
    }
}
