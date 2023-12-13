<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Event\ElasticSearch;

use App\Messenger\Message\Command\ElasticSearch as ElasticSearchCommands;
use App\Messenger\Message\Event\Lifecycle as LifecycleEvents;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

/**
 * Re-indexes.
 */
final class ReIndexSellerCompanyAfterUpdateSubscriber implements MessageSubscriberInterface
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
     * Handles the event when seller company is updated.
     */
    public function onSellerCompanyUpdate(LifecycleEvents\UserUpdatedSellerCompanyEvent $message): void
    {
        $this->reIndexCompany($message->getCompanyId());
    }

    /**
     * Handles the event when seller related company is updated.
     */
    public function onSellerRelatedCompanyUpdate(LifecycleEvents\UserUpdatedRelatedCompanyEvent $message): void
    {
        $this->reIndexCompany($message->getCompanyId());
    }

    /**
     * Handles the event when seller company addendum is updated.
     */
    public function onSellerCompanyAddendumUpdate(LifecycleEvents\UserUpdatedCompanyAddendumEvent $message): void
    {
        $this->reIndexCompany($message->getCompanyId());
    }

    /**
     * Handles the event when seller company logo is updated.
     */
    public function onSellerCompanyLogoUpdate(LifecycleEvents\UserUpdatedSellerCompanyLogoEvent $message): void
    {
        $this->reIndexCompany($message->getCompanyId());
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield LifecycleEvents\UserUpdatedSellerCompanyEvent::class     => ['bus' => 'event.bus', 'method' => 'onSellerCompanyUpdate'];
        yield LifecycleEvents\UserUpdatedRelatedCompanyEvent::class    => ['bus' => 'event.bus', 'method' => 'onSellerRelatedCompanyUpdate'];
        yield LifecycleEvents\UserUpdatedCompanyAddendumEvent::class   => ['bus' => 'event.bus', 'method' => 'onSellerCompanyAddendumUpdate'];
        yield LifecycleEvents\UserUpdatedSellerCompanyLogoEvent::class => ['bus' => 'event.bus', 'method' => 'onSellerCompanyLogoUpdate'];
    }

    /**
     * Starts seller company re-indexing.
     */
    private function reIndexCompany(int $companyId): void
    {
        $this->commandBus->dispatch(
            new ElasticSearchCommands\ReIndexSellerCompany($companyId),
            [new DispatchAfterCurrentBusStamp(), new DelayStamp(3000), new AmqpStamp('elastic.seller_company.index')]
        );
    }
}
