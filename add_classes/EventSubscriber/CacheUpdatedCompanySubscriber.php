<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Common\DependencyInjection\ServiceLocator\LibraryLocator;
use App\Common\DependencyInjection\ServiceLocator\ModelLocator;
use App\Event\SellerCompanyUpdateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Anton Zencenco
 *
 * @todo Delete or refactor after cache refactoring
 */
class CacheUpdatedCompanySubscriber implements EventSubscriberInterface
{
    /**
     * The library locator.
     */
    protected LibraryLocator $libraryLocator;

    /**
     * The model locator.
     */
    protected ModelLocator $modelLocator;

    /**
     * @param LibraryLocator $libraryLocator the library locator
     * @param ModelLocator   $modelLocator   the model locator
     */
    public function __construct(LibraryLocator $libraryLocator, ModelLocator $modelLocator)
    {
        $this->modelLocator = $modelLocator;
        $this->libraryLocator = $libraryLocator;
    }

    /**
     * Handles the event.
     */
    public function onSellerCompanyUpdate(SellerCompanyUpdateEvent $event): void
    {
        if (!\__CACHE_ENABLE || $event->isAddendumUpdate()) {
            return;
        }

        $company = $event->getCompany();
        $companyId = $event->getCompanyId();
        $cacheKey = !empty($company['index_name']) ? "ver_sellers_main{$company['index_name']}" : "seller{$companyId}";
        $this->libraryLocator->get(TinyMVC_Library_Fastcache::class)->pool('companies')->delete($cacheKey);

        // Fallback to old cache for compatibility reasons
        /** @var \TinyMVC_Library_Cache */
        $legacyCacheManager = $this->libraryLocator->get(TinyMVC_Library_Cache::class);
        /** @var \Cache_Config_Model */
        $legacyCacheOptionsRepository = $this->modelLocator->get(\Cache_Config_Model::class);
        $cacheOptions = $legacyCacheOptionsRepository->get_cache_options(!empty($company['index_name']) ? 'ver_sellers_main' : 'seller');
        if (!empty($cacheOptions)) {
            $legacyCacheManager->init($cacheOptions);
            $legacyCacheManager->delete($cacheKey);
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // should be the last one to allow header changes by other listeners first
            SellerCompanyUpdateEvent::class => ['onSellerCompanyUpdate', -255],
        ];
    }
}
