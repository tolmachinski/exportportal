<?php

declare(strict_types=1);

namespace App\Envelope\Message;

final class BindDocumentsAndRecipientsMessage
{
    /**
     * The envelope ID.
     */
    private int $envelopeId;

    /**
     * The list of recpient-type relation.
     *
     * @var Array<int, string>
     */
    private array $recipients;

    /**
     * The list of the documents.
     *
     * @var int[]
     */
    private array $documents;

    /**
     * Creates the instance of the message.
     *
     * @param Array<int, string> $recipients the list of recpient-type relation in format [..., '\<recipientId\>' => '\<type\>']
     */
    public function __construct(int $envelopeId, array $documents = [], array $recipients = [])
    {
        $this->envelopeId = $envelopeId;
        $this->recipients = $recipients;
        $this->documents = $documents;
    }

    /**
     * Get the envelope ID.
     */
    public function getEnvelopeId(): int
    {
        return $this->envelopeId;
    }

    /**
     * The list of recpient-type relation.
     *
     * @return Array<int, string> the list in format of [..., '\<recipientId\>' => '\<type\>']
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }

    /**
     * Get the list of the documents.
     *
     * @return int[]
     */
    public function getDocuments(): array
    {
        return $this->documents;
    }
}
