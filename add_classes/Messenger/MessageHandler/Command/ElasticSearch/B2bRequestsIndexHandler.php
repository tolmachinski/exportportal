<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command\ElasticSearch;

use App\Messenger\Message\Command\ElasticSearch;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Indexes the B2B requests in the ElasticSearch.
 */
final class B2bRequestsIndexHandler implements MessageSubscriberInterface
{
    /**
     * The elastic B2B requests repository.
     */
    private \Elasticsearch_B2b_Model $elasticRepository;

    /**
     * @param \Elasticsearch_B2b_Model $elasticRepository the elastic B2B requests repository
     */
    public function __construct(\Elasticsearch_B2b_Model $elasticRepository)
    {
        $this->elasticRepository = $elasticRepository;
    }

    /**
     * Indexes the B2B request by its ID.
     */
    public function indexRequest(ElasticSearch\IndexB2bRequest $message): void
    {
        $this->elasticRepository->index($message->getRequestId());
    }

    /**
     * Re-indexes the B2B request by its ID.
     */
    public function reIndexRequest(ElasticSearch\ReIndexB2bRequest $message): void
    {
        $this->elasticRepository->updateB2bRequestById($message->getRequestId());
    }

    /**
     * Removes the B2B request by its ID.
     */
    public function removeRequest(ElasticSearch\RemoveB2bRequest $message): void
    {
        $this->elasticRepository->removeB2bRequestById($message->getRequestId());
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield ElasticSearch\IndexB2bRequest::class => ['bus' => 'command.bus', 'method' => 'indexRequest'];
        yield ElasticSearch\ReIndexB2bRequest::class => ['bus' => 'command.bus', 'method' => 'reIndexRequest'];
        yield ElasticSearch\RemoveB2bRequest::class => ['bus' => 'command.bus', 'method' => 'removeRequest'];
    }
}
