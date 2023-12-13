<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Event\EditRequest;

use App\Common\Contracts\Group\GroupType;
use App\Messenger\Message\Event\Company as CompanyEvents;
use App\Messenger\Message\Event\Lifecycle as LifecycleEvents;
use App\Messenger\Message\Event\Profile as ProfileEvents;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Proxy handler that sends the update event on specific edit request events.
 *
 * @author Anton Zencenco
 */
final class UpdateEventsProxySubscriber implements MessageSubscriberInterface
{
    /**
     * The event bus.
     */
    private MessageBusInterface $eventBus;

    /**
     * @param MessageBusInterface $eventBus the event bus
     */
    public function __construct(MessageBusInterface $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    /**
     * Handles the event when company edit request is accepted.
     */
    public function onAcceptCompanyRequest(CompanyEvents\AcceptedEditRequestEvent $message): void
    {
        $this->eventBus->dispatch(new LifecycleEvents\UserUpdatedSellerCompanyEvent($message->getUserId(), $message->getCompanyId()));
    }

    /**
     * Handles the event when profile edit request is accepted.
     */
    public function onAcceptProfileRequest(ProfileEvents\AcceptedEditRequestEvent $message): void
    {
        $this->eventBus->dispatch(new LifecycleEvents\UserUpdatedProfileEvent($message->getUserId()));
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield CompanyEvents\AcceptedEditRequestEvent::class  => ['bus' => 'event.bus', 'method' => 'onAcceptCompanyRequest'];
        yield ProfileEvents\AcceptedEditRequestEvent::class  => ['bus' => 'event.bus', 'method' => 'onAcceptProfileRequest'];
    }
}
