<?php

declare(strict_types=1);

namespace App\Envelope\Event;

use App\Envelope\Command\CommandInterface;
use App\Envelope\Exception\RemoveDocumentException;
use App\Envelope\File\File;
use App\Envelope\File\StorageInterface as FileStorageInterface;
use App\Envelope\Message\RemoveDocumentFilesMessage;
use ArrayIterator;
use Ramsey\Uuid\Uuid;

final class RemoveDocumentFiles implements CommandInterface
{
    /**
     * The storage of the document files.
     */
    private FileStorageInterface $fileStorage;

    /**
     * Create instance of the command.
     */
    public function __construct(FileStorageInterface $fileStorage)
    {
        $this->fileStorage = $fileStorage;
    }

    /**
     * {@inheritdoc}
     *
     * @throws RemoveDocumentException if failed to remove the external documents
     */
    public function __invoke(RemoveDocumentFilesMessage $message)
    {
        $externalFiles = $message->getExternalFiles();
        if (empty($externalFiles)) {
            return;
        }

        // Wrap files into instance of the FileInterface
        $files = new ArrayIterator(\array_map(fn (string $uuid) => new File(null, Uuid::fromString($uuid)), $externalFiles));
        // Remove files and check if all files were deleted.
        foreach ($this->fileStorage->removeFiles($files) as list($isDeleted, $e)) {
            if (!$isDeleted) {
                throw new RemoveDocumentException('Failed to delete one of the external documents.', 0, $e ?? null);
            }
        }
    }
}
