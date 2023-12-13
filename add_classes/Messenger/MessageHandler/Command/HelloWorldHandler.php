<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command;

use App\Messenger\Message\Command\SayHelloWorld;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Prints the legendary command.
 *
 * @author Anton Zencenco
 */
class HelloWorldHandler implements MessageSubscriberInterface
{
    public function __invoke(SayHelloWorld $message)
    {
        echo 'Hello world!' . PHP_EOL;
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield SayHelloWorld::class => ['bus' => 'command.bus'];
    }
}
