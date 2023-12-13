<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler;

use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Sends the email message to the transport.
 *
 * @author Anton Zencenco
 */
class EmailMessageHandler implements MessageSubscriberInterface
{
    /**
     * The email transport.
     */
    private $transport;

    /**
     * @param TransportInterface $transport the email transport
     */
    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * Handles message.
     */
    public function __invoke(SendEmailMessage $message): ?SentMessage
    {
        return $this->transport->send($message->getMessage(), $message->getEnvelope());
    }

    public static function getHandledMessages(): iterable
    {
        yield SendEmailMessage::class;
    }
}
