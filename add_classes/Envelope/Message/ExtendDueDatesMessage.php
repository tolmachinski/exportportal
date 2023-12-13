<?php

declare(strict_types=1);

namespace App\Envelope\Message;

final class ExtendDueDatesMessage
{
    /**
     * The envelope ID.
     */
    private int $envelopeId;

    /**
     * The draft envelope recipients.
     */
    private array $recipients;

    public function __construct(int $envelopeId, array $recipients)
    {
        $this->recipients = $recipients;
        $this->envelopeId = $envelopeId;
    }

    /**
     * Get the draft envelope ID.
     */
    public function getEnvelopeId(): ?int
    {
        return $this->envelopeId;
    }

    /**
     * Get the draft envelope recipients.
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }
}
