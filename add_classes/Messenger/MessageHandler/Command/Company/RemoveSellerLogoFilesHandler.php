<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command\Company;

use App\Messenger\Message\Command\Company as CompanyCommands;
use App\Services\Company\CompanyMediaProcessorService;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Removes the seller's company log files (image and thumbs).
 *
 * @author Anton Zencenco
 */
final class RemoveSellerLogoFilesHandler implements MessageSubscriberInterface
{
    /**
     * The media processor.
     */
    private CompanyMediaProcessorService $mediaProcessor;

    /**
     * Create message handler.
     *
     * @param CompanyMediaProcessorService $mediaProcessor the media processor
     */
    public function __construct(CompanyMediaProcessorService $mediaProcessor)
    {
        $this->mediaProcessor = $mediaProcessor;
    }

    /**
     * Handle the message.
     */
    public function __invoke(CompanyCommands\RemoveSellerFiles $message)
    {
        // Use media processor to remove company lofo files.
        $this->mediaProcessor->removeCompanyFiles($message->getCompanyId(), $message->getFiles());
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield CompanyCommands\RemoveSellerFiles::class => ['bus' => 'command.bus'];
        yield CompanyCommands\RemoveSellerLogoFiles::class => ['bus' => 'command.bus'];
    }
}
