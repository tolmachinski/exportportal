<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler;

use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Transport\TransportInterface;

/**
 * Sends the chat message to the transport.
 *
 * @author Anton Zencenco
 */
final class ChatMessageHandler implements MessageSubscriberInterface
{
    /**
     * The message transport.
     */
    private TransportInterface $transport;

    /**
     * @param TransportInterface $transport the message transport
     */
    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * Handles message.
     */
    public function __invoke(MessageInterface $message): ?SentMessage
    {
        return $this->transport->send($message);
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield ChatMessage::class;
    }
}
