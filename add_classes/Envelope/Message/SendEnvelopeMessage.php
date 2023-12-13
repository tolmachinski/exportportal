<?php

declare(strict_types=1);

namespace App\Envelope\Message;

final class SendEnvelopeMessage
{
    use AccessRulesAwareTrait;

    /**
     * The envelope ID.
     */
    private ?int $envelopeId;

    /**
     * The sender ID.
     */
    private int $senderId;

    public function __construct(?int $envelopeId, int $senderId)
    {
        $this->senderId = $senderId;
        $this->envelopeId = $envelopeId;
    }

    /**
     * Get the envelope ID.
     */
    public function getEnvelopeId(): ?int
    {
        return $this->envelopeId;
    }

    /**
     * Get the sender ID.
     */
    public function getSenderId(): int
    {
        return $this->senderId;
    }
}
