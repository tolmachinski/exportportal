<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command\Profile;

use App\Messenger\Message\Command\Profile as ProfileCommands;
use App\Services\EditRequest\ProfileEditRequestDocumentsService;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Adds the file metadata to the profile edit request document.
 *
 * @author Anton Zencenco
 */
final class AcceptTemporaryFileHandler implements MessageSubscriberInterface
{
    /**
     * The profile edit requests documents service.
     */
    private ProfileEditRequestDocumentsService $documentsService;

    /**
     * @param ProfileEditRequestDocumentsService $documentsService the profile edit requests documents service
     */
    public function __construct(ProfileEditRequestDocumentsService $documentsService)
    {
        $this->documentsService = $documentsService;
    }

    /**
     * Handles the metadata processing.
     */
    public function __invoke(ProfileCommands\AcceptTemporaryFile $messenger): void
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
        yield ProfileCommands\AcceptTemporaryFile::class => ['bus' => 'command.bus'];
    }
}
