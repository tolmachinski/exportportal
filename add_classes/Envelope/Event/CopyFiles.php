<?php

declare(strict_types=1);

namespace App\Envelope\Event;

use App\Envelope\Message\BindDocumentsAndRecipientsMessage;
use App\Envelope\Message\CopyFilesMessage;
use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;

class CopyFiles extends StoreDocumentFiles
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(CopyFilesMessage $message)
    {
        $files = $message->getFiles();
        if (\count($files) > 1) {
            throw new InvalidArgumentException('Saving more then one file at once is not yet supported by this event.');
        }

        // Write files
        $writtenFiles = $this->fileStorage->copyFiles(
            $this->fileStorage->getFiles(new ArrayCollection($files)),
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
