<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command\Company;

use App\Messenger\Message\Command\Company as CompanyCommands;
use App\Services\Company\CompanyMediaProcessorService;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Updates the company logo.
 *
 * @author Anton Zencenco
 */
final class UpdateSellerLogoHandler implements MessageSubscriberInterface
{
    /**
     * The company media processor service.
     */
    private CompanyMediaProcessorService $mediaProcessor;

    /**
     * @param CompanyMediaProcessorService $mediaProcessor the company media processor service
     */
    public function __construct(CompanyMediaProcessorService $mediaProcessor)
    {
        $this->mediaProcessor = $mediaProcessor;
    }

    /**
     * Handles the bus event.
     */
    public function __invoke(CompanyCommands\UpdateSellerLogo $message): void
    {
        // Update the company logo.
        $this->mediaProcessor->updateCompanyLogo($message->getCompanyId(), $message->getPath(), $message->isTemporary());
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield CompanyCommands\UpdateSellerLogo::class => ['bus' => 'command.bus'];
    }
}
