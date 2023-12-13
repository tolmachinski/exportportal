<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Event\ElasticSearch;

use App\Messenger\Message\Command\ElasticSearch as ElasticSearchCommands;
use App\Messenger\Message\Event\Lifecycle as LifecycleEvents;
use B2b_Requests_Model as B2bRequestsRepository;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

/**
 * Re-indexes the the B2B requests when company is updated.
 *
 * @author Anton Zencenco
 */
final class ReIndexB2bRequestsSubscriber implements MessageSubscriberInterface
{
    /**
     * The B2B requests model.
     */
    private B2bRequestsRepository $requestsRepository;

    /**
     * The command bus.
     */
    private MessageBusInterface $commandBus;

    /**
     * @param MessageBusInterface   $commandBus         the command bus
     * @param B2bRequestsRepository $requestsRepository the B2B requests model
     */
    public function __construct(MessageBusInterface $commandBus, B2bRequestsRepository $requestsRepository)
    {
        $this->commandBus = $commandBus;
        $this->requestsRepository = $requestsRepository;
    }

    /**
     * Handles the event when user company is updated.
     */
    public function onCompanyUpdate(LifecycleEvents\UserUpdatedSellerCompanyEvent $message): void
    {
        $this->reIndexRequests($message->getCompanyId());
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield LifecycleEvents\UserUpdatedCompanyEvent::class       => ['bus' => 'event.bus', 'method' => 'onCompanyUpdate'];
        yield LifecycleEvents\UserUpdatedSellerCompanyEvent::class => ['bus' => 'event.bus', 'method' => 'onCompanyUpdate'];
    }

    /**
     * Re-index requests.
     */
    private function reIndexRequests(int $companyId): void
    {
        foreach ($this->getRequestsIds([$companyId]) as $requestId) {
            $this->commandBus->dispatch(
                new ElasticSearchCommands\ReIndexB2bRequest($requestId),
                [new DispatchAfterCurrentBusStamp(), new DelayStamp(3000), new AmqpStamp('elastic.b2b_request.index')]
            );
        }
    }

    /**
     * Get the requests iterator.
     *
     * @param int[] $companyIds the list of company IDs
     */
    private function getRequestsIds(array $companyIds): iterable
    {
        foreach ($this->requestsRepository->findAllBy(['scopes' => ['companies' => $companyIds]]) ?? [] as $request) {
            yield $request[$this->requestsRepository->getPrimaryKey()] => $request['id_request'];
        }
    }
}
