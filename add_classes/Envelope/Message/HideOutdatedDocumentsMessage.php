<?php

declare(strict_types=1);

namespace App\Envelope\Message;

final class HideOutdatedDocumentsMessage
{
    /**
     * The envelope ID.
     */
    private int $envelopeId;

    /**
     * The list of recpients.
     *
     * @var int[]
     */
    private array $recipients;

    /**
     * Creates the instance of the message.
     */
    public function __construct(int $envelopeId, array $recipients = [])
    {
        $this->envelopeId = $envelopeId;
        $this->recipients = $recipients;
    }

    /**
     * Get the envelope ID.
     */
    public function getEnvelopeId(): int
    {
        return $this->envelopeId;
    }

    /**
     * The list of recipients.
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }
}
