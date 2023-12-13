<?php

declare(strict_types=1);

namespace App\Envelope\Message;

final class DeclineSignedEnvelopeMessage
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

    /**
     * The decline reason text.
     */
    private ?string $declineReason;

    public function __construct(?int $envelopeId, int $senderId, ?string $declineReason = null)
    {
        $this->senderId = $senderId;
        $this->envelopeId = $envelopeId;
        $this->declineReason = $declineReason;
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

    /**
     * Get the decline reason text.
     */
    public function getDeclineReason(): ?string
    {
        return $this->declineReason;
    }
}
