<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command\ElasticSearch;

use App\Messenger\Message\Command\ElasticSearch;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Indexes the seller company in the ElasticSearch.
 */
final class SellerCompanyIndexHandler implements MessageSubscriberInterface
{
    /**
     * The elastic B2B requests repository.
     */
    private \Elasticsearch_Company_Model $elasticRepository;

    /**
     * @param \Elasticsearch_Company_Model $elasticRepository the elastic seller company repository
     */
    public function __construct(\Elasticsearch_Company_Model $elasticRepository)
    {
        $this->elasticRepository = $elasticRepository;
    }

    /**
     * Indexes the seller company by its ID.
     */
    public function indexCompany(ElasticSearch\IndexSellerCompany $message): void
    {
        $this->elasticRepository->index_company($message->getCompanyId());
    }

    /**
     * Re-indexes the seller company by its ID.
     */
    public function reIndexCompany(ElasticSearch\ReIndexSellerCompany $message): void
    {
        $this->elasticRepository->index_company($message->getCompanyId());
    }

    /**
     * Removes the seller company by its ID.
     */
    public function removeCompany(ElasticSearch\RemoveSellerCompany $message): void
    {
        $this->elasticRepository->removeCompany($message->getCompanyId());
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield ElasticSearch\IndexSellerCompany::class   => ['bus' => 'command.bus', 'method' => 'indexCompany'];
        yield ElasticSearch\ReIndexSellerCompany::class => ['bus' => 'command.bus', 'method' => 'reIndexCompany'];
        yield ElasticSearch\RemoveSellerCompany::class  => ['bus' => 'command.bus', 'method' => 'removeCompany'];
    }
}
