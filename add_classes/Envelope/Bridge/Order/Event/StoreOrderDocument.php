<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Order\Event;

use App\Common\Database\Model;
use App\Envelope\Bridge\Order\DocumentMakerInterface;
use App\Envelope\Bridge\Order\Message\StoreOrderDocumentMessage;
use App\Envelope\Event\BindDocumentsAndRecipients;
use App\Envelope\Event\StoreDocumentFiles;
use App\Envelope\File\StorageInterface as FileStorageInterface;
use App\Envelope\Message\BindDocumentsAndRecipientsMessage;

class StoreOrderDocument extends StoreDocumentFiles
{
    /**
     * The contract maker instance.
     */
    protected DocumentMakerInterface $documentMaker;

    /**
     * Create instance of the command.
     */
    public function __construct(Model $documentsRepository, FileStorageInterface $fileStorage, DocumentMakerInterface $documentMaker)
    {
        parent::__construct($documentsRepository, $fileStorage);

        $this->documentMaker = $documentMaker;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(StoreOrderDocumentMessage $message)
    {
        // Write files
        $fileContent = $this->documentMaker->make($message->getOrderId(), $message->getName());
        $fileStream = fopen('php://memory', 'r+');
        fwrite($fileStream, $fileContent);
        rewind($fileStream);

        try {
            $writtenFiles = [
                $this->fileStorage->createFile(
                    $message->getUserId(),
                    $fileStream,
                    $message->getName(),
                    $message->getType(),
                    $message->getAssignees()
                ),
            ];
        } finally {
            \fclose($fileStream);
        }

        // Save documents
        $createdDocuments = $this->saveDocuments(
            $writtenFiles,
            $message->getEnvelopeId(),
            $message->getParentDocumentId(),
            $message->getLabel(),
            $message->isAuthoriative()
        );

        // If we have a set of recipients we need to do some additional operations
        if (!empty($recipients = $message->getRecipients())) {
            (new BindDocumentsAndRecipients($this->documentsRepository))->__invoke(
                new BindDocumentsAndRecipientsMessage(
                    $message->getEnvelopeId(),
                    \array_column($createdDocuments, 'id'),
                    $recipients
                )
            );
        }

        return $createdDocuments;
    }
}
