<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command\Company;

use App\Messenger\Message\Command\Company as CompanyCommands;
use App\Services\EditRequest\CompanyEditRequestDocumentsService;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Adds the file metadata to the company edit request document.
 *
 * @author Anton Zencenco
 */
final class AcceptTemporaryFileHandler implements MessageSubscriberInterface
{
    /**
     * The company edit requests documents service.
     */
    private CompanyEditRequestDocumentsService $documentsService;

    /**
     * @param CompanyEditRequestDocumentsService $documentsService the company edit requests documents service
     */
    public function __construct(CompanyEditRequestDocumentsService $documentsService)
    {
        $this->documentsService = $documentsService;
    }

    /**
     * Handles the metadata processing.
     */
    public function __invoke(CompanyCommands\AcceptTemporaryFile $messenger): void
    {
        $this->documentsService->updateDocumentMetdata(
            $this->documentsService->acceptTemporaryDocument($messenger->getFileId(), $messenger->getUserId()),
            $messenger->getDocumentId(),
            $messenger->getUserId()
        );
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield CompanyCommands\AcceptTemporaryFile::class => ['bus' => 'command.bus'];
    }
}
