<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler;

use ExportPortal\Contracts\Notifier\Message\StorageMessage;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Transport\TransportInterface;

/**
 * Sends the storage message to the transport.
 *
 * @author Anton Zencenco
 */
final class StorageMessageHandler implements MessageSubscriberInterface
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
        yield StorageMessage::class;
    }
}
