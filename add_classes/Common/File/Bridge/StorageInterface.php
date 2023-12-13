<?php

declare(strict_types=1);

namespace App\Common\File\Bridge;

use App\Common\Exceptions\AccessDeniedException;
use App\Common\Exceptions\NotFoundException;
use Ramsey\Uuid\UuidInterface;
use Throwable;

interface StorageInterface
{
    /**
     * Get the storage path perfix.
     */
    public function getStoragePathPrefix(): string;

    /**
     * Get list of files from list.
     *
     * @param iterable<string|UuidInterface> $filesIds
     *
     * @return iterable<FileInterface>
     */
    public function getFiles(iterable $filesIds): iterable;

    /**
     * Get list of temporary files from list.
     *
     * @param iterable<string|UuidInterface> $temporaryFilesIds
     *
     * @throws NotFoundException if at least one of the files is not found
     *
     * @return iterable<FileInterface>
     */
    public function getTemporaryFiles(iterable $temporaryFilesIds): iterable;

    /**
     * Get the file access token.
     *
     * @param iterable<string|UuidInterface> $fileIds
     * @param int|string                     $userId
     *
     * @throws NotFoundException     if at least one of the files is not found in the storage
     * @throws AccessDeniedException if user has no permission to read at least one of the files
     *
     * @return iterable<FileInterface, ReferenceInterface>
     */
    public function getFilesAccessTokens(iterable $fileIds, $userId, int $timeout = 90): iterable;

    /**
     * Export document to storage. Returns the kye-value iterator where the key is temporary file ID and value is file object.
     *
     * @param iterable<FileInterface> $temporaryFiles
     *
     * @return iterable<UuidInterface, FileInterface>
     */
    public function writeTemporaryFiles(iterable $temporaryFiles, int $senderId, array $recipients): iterable;

    /**
     * Copies the document int the storage. Returns the kye-value iterator where the key is temporary file ID and value is file object.
     *
     * @param iterable<FileInterface> $sourceFiles
     *
     * @return iterable<UuidInterface, FileInterface>
     */
    public function copyFiles(iterable $sourceFiles, int $senderId, array $recipients): iterable;

    /**
     * Creates the file in storage from the resource stream.
     *
     * @param int|string $userId
     * @param resource   $fileStream
     */
    public function createFile($userId, $fileStream, string $name, ?string $type, array $recipients): FileInterface;

    /**
     * Removes the files from storage.
     *
     * @param iterable<FileInterface> $files
     *
     * @return iterable<FileInterface, array<bool, Throwable>>
     */
    public function removeFiles(iterable $files): iterable;
}
