<?php

declare(strict_types=1);

use ExportPortal\Bridge\Symfony\Component\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Library Messenger.
 *
 * @author Tatiana Bendiucov
 * @deprecated [01.12.2021]
 * @see $container->get(MessengerInterface::class) or $container->get('messenger')
 */
class TinyMVC_Library_Messenger implements MessengerInterface
{
    /**
     * The internal messenger.
     */
    private MessengerInterface $internalMessenger;

    /**
     * Library Messenger constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        if (!interface_exists(MessageBusInterface::class)) {
            throw new LogicException(
                'Messenger support cannot be enabled as the Messenger component is not installed. Try running "composer require symfony/messenger".'
            );
        }

        $this->internalMessenger = $container->get(MessengerInterface::class);
    }

    /**
     * {@inheritDoc}
     */
    public function bus(?string $busId = null): MessageBusInterface
    {
        return $this->internalMessenger->bus($busId);
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch($message, array $stamps = []): Envelope
    {
        return $this->internalMessenger->dispatch($message, $stamps);
    }
}

// End of file tinymvc_library_messenger.php
// Location: /tinymvc/myapp/plugins/tinymvc_library_messenger.php
