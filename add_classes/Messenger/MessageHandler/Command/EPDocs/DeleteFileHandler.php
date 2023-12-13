<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command\EPDocs;

use App\Messenger\Message\Command\EPDocs;
use App\Plugins\EPDocs\Rest\Resources\File as FileResource;
use App\Plugins\EPDocs\Rest\Resources\FilePermissions as FilePermissionsResource;
use App\Plugins\EPDocs\Rest\Resources\User as UserResource;
use App\Plugins\EPDocs\Rest\RestClient as DocumentRestClient;
use App\Plugins\EPDocs\Util;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Deletes the file from EPDocs server.
 *
 * @author Anton Zencenco
 */
final class DeleteFileHandler implements MessageSubscriberInterface
{
    /**
     * The API client for working with remote documents.
     */
    private DocumentRestClient $documentsApiClient;

    /**
     * @param DocumentRestClient $documentsApiClient the API client for working with remote documents
     */
    public function __construct(DocumentRestClient $documentsApiClient)
    {
        $this->documentsApiClient = $documentsApiClient;
    }

    /**
     * Handles the file deletion message.
     */
    public function __invoke(EPDocs\DeleteFile $message): void
    {
        /** @var UserResource $users */
        $users = $this->documentsApiClient->getResource(UserResource::class);
        /** @var FileResource $files */
        $files = $this->documentsApiClient->getResource(FileResource::class);
        /** @var FilePermissionsResource $filePermissions */
        $filePermissions = $this->documentsApiClient->getResource(FilePermissionsResource::class);

        // Get user API object
        if (null === ($userId = $message->getUserId())) {
            $userId = $this->documentsApiClient->getConfiguration()->getDefaultUserId();
        }

        try {
            $user = $users->findUserIfNotCreate(Util::createContext(
                $userId,
                $this->documentsApiClient->getConfiguration()->getHttpOrigin()
            ));

            if (!$files->hasFile($fileId = $message->getFileId())) {
                // The file was not found, so there is no reason to continue.
                // Plus, this operation is idempotent, so we can consider tis operation successfull.
                $this->documentsApiClient->getLogger()->info(\sprintf('The file "%s" does not exists on the server.', (string) $fileId), [
                    'fileId' => (string) $fileId,
                    'server' => $this->documentsApiClient->getConfiguration()->getHttpOrigin(),
                ]);

                return;
            }

            // Next we need to check if user has access to the file.
            if (!$filePermissions->hasPermissions($fileId, $user->getId(), FilePermissionsResource::PERMISSION_WRITE)) {
                $this->documentsApiClient->getLogger()->error(
                    \sprintf('Access to the file "%s" is not granted for user "%s"', (string) $fileId, null !== $message->getUserId() ? $userId : 'generic'),
                    [
                        'userId' => $userId,
                        'fileId' => (string) $fileId,
                        'server' => $this->documentsApiClient->getConfiguration()->getHttpOrigin(),
                    ]
                );

                // When user has no acces, we need stop the bus from retrying the command.
                throw new UnrecoverableMessageHandlingException(
                    \sprintf(
                        'The user "%s" doesn\'t have access to the file "%s"',
                        null !== $message->getUserId() ? $userId : 'generic',
                        $fileId
                    )
                );
            }

            // If file exists and access is granted, ther we can delete file
            $files->deleteFile($fileId);
        } catch (\Exception $e) {
            // File not deleted for unknwon reasons.
            // The better strategy is to log the exception and roll up the exception.
            $this->documentsApiClient->getLogger()->error(
                \sprintf('Failed to remove file "%s" due to reason: %s', (string) $fileId, $e->getMessage()),
                [
                    'fileId' => (string) $fileId,
                    'server' => $this->documentsApiClient->getConfiguration()->getHttpOrigin(),
                ]
            );

            throw $e;
        }

        $this->documentsApiClient->getLogger()->debug(\sprintf('The file "%s" has been deleted from the server.', (string) $fileId), [
            'fileId' => (string) $fileId,
            'server' => $this->documentsApiClient->getConfiguration()->getHttpOrigin(),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield EPDocs\DeleteFile::class => ['bus' => 'command.bus'];
    }
}
