<?php

declare(strict_types=1);

namespace App\Envelope\Event;

use App\Common\Database\Model;
use App\Envelope\Command\CommandInterface;
use App\Envelope\Exception\RemoveDocumentException;
use App\Envelope\Exception\WriteDocumentException;
use App\Envelope\Exception\WriteRecipientException;
use App\Envelope\File\FileInterface;
use App\Envelope\File\StorageInterface as FileStorageInterface;
use App\Envelope\Message\RemoveDocumentFilesMessage;
use App\Plugins\EPDocs\EPDocsException;
use Ramsey\Uuid\Uuid;
use Throwable;

abstract class StoreDocumentFiles implements CommandInterface
{
    /**
     * The envelope documents repository.
     */
    protected Model $documentsRepository;

    /**
     * The storage for the document files.
     */
    protected FileStorageInterface $fileStorage;

    /**
     * Create instance of the command.
     */
    public function __construct(Model $documentsRepository, FileStorageInterface $fileStorage)
    {
        $this->fileStorage = $fileStorage;
        $this->documentsRepository = $documentsRepository;
    }

    /**
     * Saves the documents.
     *
     * @param iterable<FileInterface> $files
     *
     * @throws WriteRecipientException if failed to write the envelope documents
     * @throws EPDocsException         if failed to write the EPDocs
     */
    protected function saveDocuments(iterable $files, int $envelopeId, ?int $parentEnvelopeId, ?string $label, bool $isAuthoriative = false): array
    {
        //region Export Files
        $newDocuments = [];
        $documentList = [];

        try {
            // Collect the files information
            foreach ($files as $file) {
                $newDocuments[] = $fileUuid = Uuid::uuid6();
                $documentList[] = [
                    'id_envelope'          => $envelopeId,
                    'id_parent_document'   => $parentEnvelopeId,
                    'uuid'                 => $fileUuid,
                    'label'                => $label,
                    'file_size'            => $file->getSize(),
                    'file_name'            => $file->getName(),
                    'file_extension'       => $file->getExtension(),
                    'file_original_name'   => $file->getOriginalName(),
                    'mime_type'            => $file->getType(),
                    'display_name'         => $file->getOriginalName(),
                    'internal_name'        => sprintf('E%s-F-%s', $envelopeId, base64_encode((string) $fileUuid)),
                    'remote_uuid'          => $file->getUuid(),
                    'is_authoriative_copy' => $isAuthoriative,
                ];
            }

            //region Write Documents
            try {
                $isSaved = (bool) $this->documentsRepository->insertMany($documentList);
            } catch (Throwable $e) {
                // Pass - handle below.
            }

            if (!$isSaved) {
                throw new WriteDocumentException('Failed to write the documents into database', 0, $e ?? null);
            }
            //endregion Write Document
        } catch (Throwable $e) {
            // @todo Log this exception
            try {
                (new RemoveDocumentFiles($this->fileStorage))(new RemoveDocumentFilesMessage($newDocuments));
            } catch (RemoveDocumentException $e) {
                // @todo Log this exception
            }

            // Roll exception to the top level.
            throw $e;
        }
        //endregion Export Files

        return $this->documentsRepository->findAllBy([
            'conditions' => [
                'envelope' => $envelopeId,
                'uuids'    => $newDocuments,
            ],
        ]);
    }
}
