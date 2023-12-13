<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command\Company;

use App\Common\Contracts\Group\GroupType;
use App\Messenger\Message\Command\Company as CompanyCommands;
use App\Services\Company\CompanyMediaProcessorService;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Updates the company video information.
 *
 * @author Anton Zencenco
 */
final class UpdateCompanyVideoHandler implements MessageSubscriberInterface
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
    public function __invoke(CompanyCommands\UpdateCompanyVideo $message): void
    {
        // Leave if not seller or shipper
        if (!\in_array($message->getGroupType(), [GroupType::SELLER(), GroupType::SHIPPER()])) {
            return;
        }
        // The part for shipper is not implemented
        if ($message->getGroupType() === GroupType::SHIPPER()) {
            throw new \LogicException('This part is not implemented yet');
        }

        // Update the company video.
        $this->mediaProcessor->updateCompanyVideo($message->getCompanyId(), $message->getUrl());
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield CompanyCommands\UpdateCompanyVideo::class => ['bus' => 'command.bus'];
    }
}
