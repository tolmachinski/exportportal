<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Event\Product;

use App\Messenger\Message\Event\Product\ProductInStockEvent;
use App\Messenger\Message\Event\Product\ProductOutOfStockEvent;
use App\Messenger\Message\Event\Product\ProductPendingRequestEvent;
use App\Messenger\Message\Event\Product\ProductPriceChangedEvent;
use App\Messenger\Message\Event\Product\ProductWasDraftEvent;
use App\Messenger\Message\Event\Product\ProductWasUpdatedEvent;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Triggers different granular events on product update.
 */
final class TriggerGranularActionsWhenProductWasUpdated implements MessageSubscriberInterface
{
    /**
     * The event bus instance.
     */
    private MessageBusInterface $eventBus;

    /**
     * @param MessageBusInterface $eventBus the event bus instance
     */
    public function __construct(MessageBusInterface $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(ProductWasUpdatedEvent $message): void
    {
        $changes = $message->getChanges();
        if (isset($changes['moderate_request']) && $changes['moderate_request']) {
            $this->eventBus->dispatch(new ProductPendingRequestEvent($message->getProductId()));
        }

        if (isset($changes['is_out_of_stock'])) {
            if ($changes['is_out_of_stock']) {
                $this->eventBus->dispatch(new ProductOutOfStockEvent($message->getProductId()));
            } else {
                $this->eventBus->dispatch(new ProductInStockEvent($message->getProductId()));
            }
        }

        if (isset($changes['draft']) && $changes['draft']) {
            $this->eventBus->dispatch(new ProductWasDraftEvent($message->getProductId()));
        }

        if (isset($changes['final_price'])) {
            $this->eventBus->dispatch(new ProductPriceChangedEvent($message->getProductId(), $changes['final_price']));
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield ProductWasUpdatedEvent::class => ['bus' => 'event.bus'];
    }
}
