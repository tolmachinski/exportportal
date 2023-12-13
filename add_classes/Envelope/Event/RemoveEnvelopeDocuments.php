<?php

declare(strict_types=1);

namespace App\Envelope\Event;

use App\Common\Database\Model;
use App\Envelope\Command\CommandInterface;
use App\Envelope\Exception\RemoveDocumentException;
use App\Envelope\File\StorageInterface as FileStorageInterface;
use App\Envelope\Message\RemoveDocumentFilesMessage;
use App\Envelope\Message\RemoveEnvelopeComponentsMessage;
use App\Plugins\EPDocs\ApiAwareTrait;
use Exception;

final class RemoveEnvelopeDocuments implements CommandInterface
{
    use ApiAwareTrait;

    /**
     * The envelope documents repository.
     */
    private Model $documentsRepository;

    /**
     * The storage for the document files.
     */
    private FileStorageInterface $fileStorage;

    /**
     * Create instance of the command.
     */
    public function __construct(Model $documentsRepository, FileStorageInterface $fileStorage)
    {
        $this->fileStorage = $fileStorage;
        $this->documentsRepository = $documentsRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @throws RemoveDocumentException if failed to remove the documents
     */
    public function __invoke(RemoveEnvelopeComponentsMessage $message)
    {
        //region Remove External Documents
        (new RemoveDocumentFiles($this->fileStorage))(
            new RemoveDocumentFilesMessage(
                \array_column(
                    $this->documentsRepository->findAllBy([
                        'columns'    => ['remote_uuid'],
                        'conditions' => [
                            'envelope' => $message->getEnvelopeId(),
                        ],
                    ]),
                    'remote_uuid'
                )
            )
        );
        //endregion Remove External Documents

        //region Remove Documents Records
        try {
            $isDeleted = (bool) $this->documentsRepository->deleteAllBy([
                'conditions' => [
                    'envelope' => $message->getEnvelopeId(),
                ],
            ]);
        } catch (Exception $e) {
            // Pass - handle below.
        }

        if (!$isDeleted) {
            throw new RemoveDocumentException('Failed to remove the documents from the database.', 0, $e ?? null);
        }
        //endregion Remove Documents Records
    }
}
