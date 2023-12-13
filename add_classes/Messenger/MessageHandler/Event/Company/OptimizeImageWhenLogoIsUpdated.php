<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Event\Company;

use App\Messenger\Message\Event\Lifecycle as LifecycleEvents;
use Image_optimization_Model;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Optimizes the image when company (seller or shipper) logo was updated.
 *
 * @author Anton Zencenco
 */
final class OptimizeImageWhenLogoIsUpdated implements MessageSubscriberInterface
{
    /**
     * The optimization repository.
     */
    private Image_optimization_Model $optimizationRepository;

    /**
     * @param Image_optimization_Model $optimizationRepository the optimization repository
     */
    public function __construct(Image_optimization_Model $optimizationRepository)
    {
        $this->optimizationRepository = $optimizationRepository;
    }

    /**
     * Handles the event when company log updated.
     */
    public function onSellerLogoUpdate(LifecycleEvents\UserUpdatedSellerCompanyLogoEvent $message): void
    {
        // If path is empty, then exit.
        if (null === $message->getLogoPath()) {
            return;
        }

        // Optimize image
        $this->optimizationRepository->insertOne([
            'file_path'	=> $message->getLogoPath(),
            'context'   => ['id_company' => $message->getCompanyId()],
            'type'      => 'company_logo',
        ]);
    }

    /**
     * Handles the event when company log updated.
     */
    public function onShipperLogoUpdate(LifecycleEvents\UserUpdatedShipperCompanyLogoEvent $message): void
    {
        // If path is empty, then exit.
        if (null === $message->getLogoPath()) {
            return;
        }

        // Optimize image
        $this->optimizationRepository->insertOne([
            'file_path'	=> $message->getLogoPath(),
            'context'   => ['id_company' => $message->getCompanyId()],
            'type'      => 'shipper_company_logo',
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield LifecycleEvents\UserUpdatedSellerCompanyLogoEvent::class  => ['bus' => 'event.bus', 'method' => 'onSellerLogoUpdate'];
        yield LifecycleEvents\UserUpdatedShipperCompanyLogoEvent::class => ['bus' => 'event.bus', 'method' => 'onShipperLogoUpdate'];
        yield LifecycleEvents\UserUpdatedCompanyLogoEvent::class        => ['bus' => 'event.bus', 'method' => 'onSellerLogoUpdate'];
    }
}
