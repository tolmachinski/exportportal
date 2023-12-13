<?php

declare(strict_types=1);

namespace App\Envelope\Message;

final class ViewEnvelopeMessage
{
    use AccessRulesAwareTrait;

    /**
     * The envelope ID.
     */
    private ?int $envelopeId;

    /**
     * The recipient ID.
     */
    private int $recipientId;

    public function __construct(?int $envelopeId, int $recipientId)
    {
        $this->envelopeId = $envelopeId;
        $this->recipientId = $recipientId;
    }

    /**
     * Get the envelope ID.
     */
    public function getEnvelopeId(): ?int
    {
        return $this->envelopeId;
    }

    /**
     * Get the recipient ID.
     */
    public function getRecipientId(): int
    {
        return $this->recipientId;
    }
}
