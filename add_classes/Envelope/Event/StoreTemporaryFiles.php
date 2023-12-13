<?php

declare(strict_types=1);

namespace App\Envelope\Event;

use App\Envelope\Message\BindDocumentsAndRecipientsMessage;
use App\Envelope\Message\StoreTemporaryFilesMessage;
use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;

class StoreTemporaryFiles extends StoreDocumentFiles
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(StoreTemporaryFilesMessage $message)
    {
        $temporaryFiles = $message->getTemporaryFiles();
        if (\count($temporaryFiles) > 1) {
            throw new InvalidArgumentException('Saving more then one temporary files at once is not yet supported by this event.');
        }

        // Write files
        $writtenFiles = $this->fileStorage->writeTemporaryFiles(
            $this->fileStorage->getTemporaryFiles(
                new ArrayCollection(
                    $temporaryFiles
                )
            ),
            $message->getSenderId(),
            $message->getAssignees()
        );

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
