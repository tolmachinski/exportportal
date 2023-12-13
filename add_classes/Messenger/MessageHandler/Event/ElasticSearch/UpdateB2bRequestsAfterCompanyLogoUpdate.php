<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Event\ElasticSearch;

use App\Messenger\Message\Event\Lifecycle as LifecycleEvents;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Updates in bulk the B2B requests after company logo update.
 *
 * @author Anton Zencenco
 */
final class UpdateB2bRequestsAfterCompanyLogoUpdate implements MessageSubscriberInterface
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
     * Handles the event when user company logo is updated.
     */
    public function __invoke(LifecycleEvents\UserUpdatedSellerCompanyLogoEvent $message): void
    {
        if (null !== ($logoPath = $message->getLogoPath())) {
            // We need to take only the name of the file
            $logoPath = \basename($logoPath);
        }

        // Update ALL records in the B2B index
        $this->elasticRepository->updateB2bCompanyLogoByCompanyId($message->getCompanyId(), $logoPath ?? '');
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield LifecycleEvents\UserUpdatedCompanyLogoEvent::class       => ['bus' => 'event.bus'];
        yield LifecycleEvents\UserUpdatedSellerCompanyLogoEvent::class => ['bus' => 'event.bus'];
    }
}
