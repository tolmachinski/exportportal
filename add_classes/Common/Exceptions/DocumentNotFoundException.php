<?php

namespace App\Common\Exceptions;

use Throwable;

class DocumentNotFoundException extends NotFoundException
{
    /**
     * The document's ID.
     *
     * @var null|mixed
     */
    private $documentId;

    /**
     * {@inheritdoc}
     */
    public function __construct($documentId = null, string $message = 'The document with provided ID is not found', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->documentId = $documentId;
    }

    /**
     * Get the document's ID.
     *
     * @return null|mixed
     */
    public function getDocumentId()
    {
        return $this->documentId;
    }

    /**
     * Set the document's ID.
     *
     * @param null|mixed $documentId the document's ID
     *
     * @return self
     */
    public function setDocumentId($documentId)
    {
        $this->documentId = $documentId;

        return $this;
    }
}
