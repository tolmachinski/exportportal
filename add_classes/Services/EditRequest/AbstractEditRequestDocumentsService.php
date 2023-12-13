<?php

declare(strict_types=1);

namespace App\Services\EditRequest;

use App\Common\Contracts\EditRequest\EditRequestStatus;
use App\Common\Database\Exceptions\WriteException;
use App\Common\Database\Model;
use App\Common\Exceptions\AccessDeniedException;
use App\Common\Exceptions\NotFoundException;
use App\Common\Exceptions\OwnershipException;
use App\Common\Exceptions\ProcessingException;
use App\Common\Exceptions\UserNotFoundException;
use App\Documents\File\File;
use App\Documents\Versioning\AbstractVersion;
use App\Documents\Versioning\AcceptedVersion;
use App\Documents\Versioning\ContentContext;
use App\Documents\Versioning\PendingVersion;
use App\Documents\Versioning\RejectedVersion;
use App\Documents\Versioning\VersionList;
use App\Messenger\Message\Command\EPDocs as EPDocsCommands;
use App\Plugins\EPDocs\Rest\Objects\AccessToken;
use App\Plugins\EPDocs\Rest\Objects\File as RemoteFile;
use App\Plugins\EPDocs\Rest\Resources\AccessToken as AccessTokenResource;
use App\Plugins\EPDocs\Rest\Resources\File as FileResource;
use App\Plugins\EPDocs\Rest\Resources\FilePermissions as FilePermissionsResource;
use App\Plugins\EPDocs\Rest\Resources\User as UserResource;
use App\Plugins\EPDocs\Rest\RestClient as DocumentRestClient;
use App\Plugins\EPDocs\Util;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

/**
 * The service that contains the abstract business logic for operations on the edit request documents.
 *
 * @author Anton Zencenco
 */
abstract class AbstractEditRequestDocumentsService
{
    use LoggerAwareTrait;

    public const ERROR_DOCUMENT_ACCESS = 11;
    public const ERROR_DOCUMENT_NOT_FOUND = 7;
    public const ERROR_DOCUMENT_REQUEST_NOT_FOUND = 8;

    /**
     * The command bus instance.
     */
    protected MessageBusInterface $commandBus;

    /**
     * The API client for working with remote documents.
     */
    protected DocumentRestClient $documentsApiClient;

    /**
     * The local repository with the verification documents.
     */
    protected Model $verificationDocumentsRepository;

    /**
     * The local repository with the request documents.
     */
    protected Model $requestDocumentsRepository;

    /**
     * The local repository with the requests.
     */
    protected Model $requestsRepository;

    public function __construct(
        Model $requestsRepository,
        Model $requestDocumentsRepository,
        Model $verificationDocumentsRepository,
        DocumentRestClient $documentsApiClient,
        MessageBusInterface $commandBus,
        ?LoggerInterface $logger = null
    ) {
        $this->commandBus = $commandBus;
        $this->documentsApiClient = $documentsApiClient;
        $this->requestsRepository = $requestsRepository;
        $this->requestDocumentsRepository = $requestDocumentsRepository;
        $this->verificationDocumentsRepository = $verificationDocumentsRepository;
        $this->setLogger($logger ?? new NullLogger());
    }

    /**
     * Determine if the edit request still has not processed requests.
     */
    public function hasNotProcessedDocuments(int $requestId): bool
    {
        return $this->requestDocumentsRepository->countAllBy(['scopes' => ['request' => $requestId, 'isPorcessed' => false]]) > 0;
    }

    /**
     * Get the remote file object for the document ID.
     *
     * @throws UserNotFoundException when user account on remote server was not found
     * @throws OwnershipException    when user has no access to the file on remote server
     */
    public function getDocumentFile(?int $documentId, int $userId = null): RemoteFile
    {
        $this->ensureDocumentForActiveRequest(
            $document = $this->getDocumentData($documentId, true)
        );

        return $this->getRemoteFileInstance($document['remote_uuid'], $userId);
    }

    /**
     * Get the download token for edit request document.
     *
     * @throws NotFoundException     when document with $documentId is not found or it is NULL
     * @throws NotFoundException     when request for document with $documentId is not found
     * @throws AccessDeniedException when trying to download document for request with invalid status
     * @throws UserNotFoundException when user account on remote server was not found
     * @throws OwnershipException    when user has no access to the file on remote server
     */
    public function getDownloadToken(RemoteFile $file, int $ttl = 90): AccessToken
    {
        //region API interactions
        /** @var AccessTokenResource $accessTokens */
        $accessTokens = $this->documentsApiClient->getResource(AccessTokenResource::class);
        //endregion Resources

        return $accessTokens->createToken($file->getId(), $ttl);
    }

    /**
     * Get the list of documents required for request.
     */
    public function getRequiredDocuments(int $userId): array
    {
        return $this->getUserExisitingDocuments($userId, true)->getValues();
    }

    /**
     * Add documents for edit requests.
     *
     * @param array{id:int,comment:string,subtitle:string,uuid:UuidInterface}[] $documentsData the list of the added documents
     *
     * @throws \LengthException when the amount of added documents is not the same as the amount of exisitng ones
     *
     * @return array{id:int,uuid:UuidInterface}[] The list of the ID-UUID tuples
     */
    public function addDocuments(int $requestId, int $userId, array $documentsData): array
    {
        //region Documents
        // Get the existing user's documents
        $existingDocuments = $this->getUserExisitingDocuments($userId);
        // If there is no documents then we have nothing to do here.
        if (0 === count($documentsData) && 0 === $existingDocuments->count()) {
            return [];
        }
        if (count($documentsData) !== $existingDocuments->count()) {
            throw new \LengthException('The amount of the documents in the request is not the same as user\'s documents amount');
        }
        //endregion Documents

        $connection = $this->requestDocumentsRepository->getConnection();
        $connection->beginTransaction();
        try {
            $savedDocuments = [];
            foreach ($documentsData as list('id' => $documentId, 'uuid' => $fileUuid, 'comment' => $comment, 'subtitle' => $subtitle)) {
                $existingDocument = $existingDocuments->filter(fn (array $d) => $d['id_document'] === $documentId)->first() ?: null;
                if (null === $existingDocument) {
                    throw new \OutOfBoundsException(
                        \sprintf('One of the documents for request "%s" is not present in user\'s documents.', $requestId)
                    );
                }

                $requestDocumentId = $this->writeDocumentToTheStorage(
                    $fileUuid,
                    $existingDocument,
                    (int) $documentId,
                    $requestId,
                    $userId,
                    $comment,
                    $subtitle
                );
                // Collect information about saved document to send it to the bus.
                $savedDocuments[] = ['id' => $requestDocumentId, 'uuid' => $fileUuid];
            }

            $connection->commit();
        } catch (\Throwable $e) {
            // Roll back the changes in the transactions
            $connection->rollBack();

            throw $e;
        }

        return $savedDocuments;
    }

    /**
     * Deletes the documents for edit request.
     *
     * @param int $requestId the edit request ID
     *
     * @throws WriteException if failed to update record
     */
    public function deleteDocuments(int $requestId): void
    {
        $commands = [];
        $documents = $this->getDocumentUuids($requestId);
        $connection = $this->requestDocumentsRepository->getConnection();
        $connection->beginTransaction();
        try {
            // First of all we need to load the documents information for request.
            // If there is no documents then this cicle will be skipped.
            foreach ($documents as $documentId => [$fileUuid, $userId]) {
                // After that, each document will be marked as deleted in the DB.
                $this->requestDocumentsRepository->updateOne($documentId, ['remote_uuid' => null, 'deleted_at_date' => new \DateTimeImmutable()]);
                if (null !== $fileUuid) {
                    $commands[] = new EPDocsCommands\DeleteFile($fileUuid, $userId);
                }
            }

            $connection->commit();
        } catch (\Throwable $e) {
            // If we failed to mark document as deleted, we will rollback the transaction.
            $connection->rollBack();
            // And after that log the error
            if ($this->logger) {
                $this->logger->error(
                    \sprintf('Failed to delete the documents for edit request with ID "%s" due to error: %s', $requestId, $e->getMessage()),
                    ['requestId' => $requestId]
                );
            }
            // And fire the exception
            throw new WriteException(
                \sprintf('Failed to update the edit request document with ID "%s" due to error: %s', $documentId ?? 'NULL', $e->getMessage()),
                0,
                $e
            );
        }

        // Log the process success
        if ($this->logger) {
            $this->logger->debug(\sprintf('Deleted the documents for the request ID "%s"', $requestId), ['requestId' => $requestId]);
        }
        // Now we need to remove files from EPD
        foreach ($commands as $command) {
            $this->commandBus->dispatch($command, [new DelayStamp(7500), new AmqpStamp("epdocs.{$this->getDocumentType()}.processing")]);
        }
    }

    /**
     * Applies document changes from the edit request.
     *
     * @throws NotFoundException    if edit request is not found
     * @throws ProcessingException  if one or more documents for request are not yet processed
     * @throws OutOfBoundsException if at least one document of required is missing
     * @throws WriteException       if failed to write the document updates
     */
    public function applyDocuments(int $requestId): void
    {
        //region Entities
        if (
            null === ($editRequest = $this->requestsRepository->findOne($requestId))
        ) {
            throw new NotFoundException(sprintf('The edit request with ID "%s" is not found', (string) ($editRequestId ?? 'NULL')));
        }
        //endregion Entities

        // And the first thing to do is to determine if we still have not processed documents
        // for the current request. If we have then documents cannot be applied - they must be processed first.
        if ($this->hasNotProcessedDocuments($requestId)) {
            throw new ProcessingException(
                \sprintf('The documents for request "%s" cannot be applied because one or more of them are not yet processed.', $requestId)
            );
        }

        //region Documents
        // Get the request documents
        /** @var null|Collection $documents */
        $documents = new ArrayCollection(
            $this->requestDocumentsRepository->findAllBy([
                'with'   => ['type'],
                'scopes' => ['request' => $requestId],
            ]) ?? []
        );
        // Get the existing user's documents
        $existingDocuments = $this->getUserExisitingDocuments($editRequest['id_user']);
        // If there is no documents then we have nothing to do here.
        if (0 === $documents->count() && 0 === $existingDocuments->count()) {
            return;
        }
        if ($documents->count() !== $existingDocuments->count()) {
            throw new \LengthException('The amount of the documents in the request is not the same as user\'s documents amount');
        }
        //endregion Documents

        $updateDocuments = [];
        foreach ($documents as $document) {
            $existingDocument = $existingDocuments->filter(fn (array $d) => $d['id_document'] === $document['id_document'])->first() ?: null;
            if (null === $existingDocument) {
                throw new \OutOfBoundsException(
                    \sprintf('One of the documents for request "%s" is not present in user\'s documents.', $requestId)
                );
            }

            /** @var VersionList $versions */
            $versions = $existingDocument['versions'] ?? new VersionList();
            /** @var null|AbstractVersion $latestVersion */
            $latestVersion = $versions->last() ?: null;
            // In the case when we have a pending document in the list
            // we need to reject it. The list must NEVER contain more than one
            // pending version.
            if (null !== $latestVersion && $latestVersion instanceof PendingVersion) {
                $versions->replace(
                    $latestVersion,
                    RejectedVersion::createFromVersion($latestVersion)
                        ->withReasonTitle('Automatiac rejection')
                        ->withReason('This version is automatiacelly rejected due to applying update from edit request')
                );
            }
            // Add new version to the list that contains metadata from request document
            $versions->add(
                (new AcceptedVersion(
                    \sprintf('v%s', (string) $versions->count() + 1),
                    $document['metadata']['comment'] ?? null,
                    null,
                    new File(
                        $document['remote_uuid'],
                        $document['metadata']['name'],
                        $document['metadata']['extension'],
                        $document['metadata']['size'],
                        $document['metadata']['mime'],
                        $document['metadata']['originalName'],
                    ),
                ))->withContext(new ContentContext(
                    \iterator_to_array($latestVersion->getContext()->getIterator()),
                    $document['type']['document_base_context'] ?? []
                ))
            );

            $updateDocuments[] = $existingDocument;
        }

        // And now we need to save changes in files SAFELLY
        // We will begin transaction and in the case of some problems, we can roll
        // back to the previous state
        $connection = $this->verificationDocumentsRepository->getConnection();
        $connection->beginTransaction();
        try {
            foreach ($updateDocuments as $document) {
                if (!$this->verificationDocumentsRepository->updateOne($document['id_document'], [
                    'subtitle' => $document['metadata']['subtitle'] ?? null,
                    'versions' => $document['versions'],
                ])) {
                    throw new WriteException(
                        \sprintf('Failed to update versions of the document with ID "%s"', $document['id_document'])
                    );
                }
            }

            // Commit transactions
            $connection->commit();
        } catch (\Throwable $e) {
            // Roll back the changes in the transactions
            $connection->rollBack();

            throw $e;
        }
    }

    /**
     * Update metadata for edit request document.
     */
    public function updateDocumentMetdata(RemoteFile $file, int $documentId, ?int $userId = null): void
    {
        // First, we need to ensure that document can be processed
        $this->ensureDocumentForActiveRequest(
            $document = $this->getDocumentData($documentId, true)
        );
        // Next, we need to check if document is already processed,
        // then we don't need to do anything
        if ($document['is_processed']) {
            return;
        }
        // After that - update the document record in DB.
        if (
            !$this->requestDocumentsRepository->updateOne($documentId, [
                'remote_uuid'  => $file->getId(),
                'is_processed' => true,
                'metadata'     => \array_merge(
                    $document['metadata'] ?? [],
                    [
                        'mime'         => $file->getType(),
                        'name'         => $file->getName(),
                        'size'         => $file->getSize(),
                        'extension'    => $file->getExtension(),
                        'originalName' => $file->getOriginalName(),
                        'internalName' => sprintf('PER%s-F-%s', $documentId, base64_encode((string) $file->getId())),
                    ]
                ),
            ])
        ) {
            throw new WriteException(
                \sprintf('Failed to add metadata for edit request document with ID "%s"', $documentId)
            );
        }
    }

    /**
     * Accepts temporary document file.
     */
    public function acceptTemporaryDocument(UuidInterface $fileUuid, ?int $userId = null): RemoteFile
    {
        //region API interactions
        //region Resources
        /** @var UserResource $users */
        $users = $this->documentsApiClient->getResource(UserResource::class);
        /** @var FileResource $files */
        $files = $this->documentsApiClient->getResource(FileResource::class);
        /** @var FilePermissionsResource $filePermissions */
        $filePermissions = $this->documentsApiClient->getResource(FilePermissionsResource::class);
        //endregion Resources

        if (
            null === ($user = $users->findUserIfNotCreate(
                Util::createContext(
                    $userId ?? $this->documentsApiClient->getConfiguration()->getDefaultUserId(),
                    $this->documentsApiClient->getConfiguration()->getHttpOrigin()
                )
            ))
        ) {
            throw new UserNotFoundException('The user with such ID is not found in EP Docs.');
        }
        if (
            null === ($manager = $users->findUserIfNotCreate(
                Util::createContext(
                    $this->documentsApiClient->getConfiguration()->getDefaultUserId(),
                    $this->documentsApiClient->getConfiguration()->getHttpOrigin()
                )
            ))
        ) {
            throw new UserNotFoundException('The user with such ID is not found in EP Docs.');
        }

        try {
            $file = $files->createFile($user->getId(), $fileUuid); // Create file
            $filePermissions->createPermissions(
                $file->getId(),
                $manager->getId(),
                FilePermissionsResource::PERMISSION_READ | FilePermissionsResource::PERMISSION_WRITE
            ); // Create permissions for manager
        } catch (\Exception $e) {
            if (isset($file)) {
                $files->deleteFile($file->getId());
            }

            throw $e;
        }

        return $file;
    }

    /**
     * Get the instance of the file from API by UUID.
     */
    protected function getRemoteFileInstance(UuidInterface $fileId, ?int $userId = null): RemoteFile
    {
        //region API interactions
        //region Resources
        /** @var UserResource $users */
        $users = $this->documentsApiClient->getResource(UserResource::class);
        /** @var FileResource $files */
        $files = $this->documentsApiClient->getResource(FileResource::class);
        /** @var FilePermissionsResource $filePermissions */
        $filePermissions = $this->documentsApiClient->getResource(FilePermissionsResource::class);
        //endregion Resources

        if (
            null === ($user = $users->findUserIfNotCreate(
                Util::createContext(
                    $userId ?? $this->documentsApiClient->getConfiguration()->getDefaultUserId(),
                    $this->documentsApiClient->getConfiguration()->getHttpOrigin()
                )
            ))
        ) {
            throw new UserNotFoundException('The user with such ID is not found in EP Docs.');
        }

        $file = $files->getFile($fileId);
        if (!$filePermissions->hasPermissions($file->getId(), $user->getId(), FilePermissionsResource::PERMISSION_READ)) {
            throw new OwnershipException(
                \sprintf('The user with ID "%s" has no access to the file "%s".', (string) $user->getId(), (string) $file->getId())
            );
        }
        //endregion API interactions

        return $file;
    }

    /**
     * Get the document data for active request.
     *
     * @throws NotFoundException when document with $documentId is not found or it is NULL
     */
    protected function getDocumentData(?int $documentId): array
    {
        if (
            null === $documentId
            || null === ($document = $this->requestDocumentsRepository->findOne($documentId, ['with' => ['type', 'request']]))
        ) {
            throw new NotFoundException(
                sprintf('The edit request document with ID "%s" is not found.', (string) ($documentId ?? 'NULL')),
                static::ERROR_DOCUMENT_NOT_FOUND
            );
        }

        return $document;
    }

    /**
     * Get information about documents.
     *
     * @return iterable<int,Uuid>
     */
    protected function getDocumentUuids(int $requestId): iterable
    {
        $documents = $this->requestDocumentsRepository->findAllBy(['scopes' => ['request' => $requestId]]);
        if (empty($documents)) {
            // Request doesn't have documents, so we have nothing to do
            return;
        }

        $primaryKey = $this->requestDocumentsRepository->getPrimaryKey();
        foreach ($documents as $document) {
            /** @var null|Uuid $fileUuid */
            $fileUuid = $document['remote_uuid'] ?? null;
            if (null === $fileUuid) {
                // If there is no remote UId that means that file was deleted.
                continue;
            }

            yield $document[$primaryKey] => [$fileUuid, $document['id_user'] ?? null];
        }
    }

    /**
     * Get the type of edit request document. Used primary in sending the document bus commands.
     */
    abstract protected function getDocumentType(): string;

    /**
     * Ensures that the document belongs to the active request.
     *
     * @throws NotFoundException     when request for document with $documentId is not found
     * @throws AccessDeniedException when trying to download document for request with invalid status
     */
    protected function ensureDocumentForActiveRequest(array $document): void
    {
        if (null === ($editRequest = $document['request'] ?? null)) {
            throw new NotFoundException(
                sprintf('The edit request with ID "%s" is not found', (string) ($document['id_request'] ?? 'NULL')),
                static::ERROR_DOCUMENT_REQUEST_NOT_FOUND
            );
        }
        if ($editRequest['status'] !== EditRequestStatus::PENDING()) {
            throw new AccessDeniedException(
                sprintf('Only documents of the requests in status "%s" can be processed', (string) EditRequestStatus::PENDING()),
                static::ERROR_DOCUMENT_ACCESS
            );
        }
    }

    /**
     * Get list of existing user's documents.
     */
    abstract protected function getUserExisitingDocuments(int $userId, bool $attachTypes = false): ArrayCollection;

    /**
     * Writes the document to the storage.
     *
     * @throws WriteException if failed to write the document to the storage
     */
    protected function writeDocumentToTheStorage(
        UuidInterface $remoteUuid,
        array $existingDocument,
        int $documentId,
        int $requestId,
        ?int $userId = null,
        ?string $comment = null,
        ?string $subtitle = null
    ): int {
        if (!(
            $requestDocumentId = $this->requestDocumentsRepository->insertOne([
                'uuid'         => Uuid::uuid6(),
                'id_type'      => $existingDocument['id_type'],
                'id_user'      => $userId,
                'id_request'   => $requestId,
                'id_document'  => $documentId,
                'remote_uuid'  => $remoteUuid,
                'is_processed' => false,
                'metadata'    => [
                    'comment'  => $comment ?? null,
                    'subtitle' => $subtitle ?? null,
                ],
            ])
        )) {
            throw new WriteException(
                \sprintf('Failed to add the document for edit request with ID "%s"', $requestId)
            );
        }

        return (int) $requestDocumentId;
    }
}
