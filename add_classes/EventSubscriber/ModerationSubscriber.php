<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\SellerCompanyUpdateEvent;
use Moderation_Model;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use const App\Moderation\Types\TYPE_COMPANY;

/**
 * @author Anton Zencenco
 *
 * @todo Delete or refactor after cache refactoring
 */
class ModerationSubscriber implements EventSubscriberInterface
{
    /**
     * The moderation repository.
     */
    protected \Moderation_Model $moderationRepository;

    /**
     * @param \ModelLocator $moderationRepository the moderation repository
     */
    public function __construct(Moderation_Model $moderationRepository)
    {
        $this->moderationRepository = $moderationRepository;
    }

    /**
     * Handles the event.
     */
    public function onSellerCompanyUpdate(SellerCompanyUpdateEvent $event): void
    {
        $this->moderationRepository->immoderate($event->getCompanyId(), TYPE_COMPANY);
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SellerCompanyUpdateEvent::class => ['onSellerCompanyUpdate', 0],
        ];
    }
}
