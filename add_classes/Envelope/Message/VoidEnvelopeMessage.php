<?php

declare(strict_types=1);

namespace App\Envelope\Message;

final class VoidEnvelopeMessage
{
    use AccessRulesAwareTrait;

    /**
     * The envelope ID.
     */
    private ?int $envelopeId;

    /**
     * The sender ID.
     */
    private ?int $senderId;

    /**
     * The reason envelope was voided.
     */
    private ?string $reason;

    /**
     * Flag that indicates if external resources must be removed as well.
     */
    private bool $removeExternals;

    public function __construct(?int $envelopeId, ?int $senderId, ?string $reason, bool $removeExternals = true)
    {
        $this->senderId = $senderId;
        $this->envelopeId = $envelopeId;
        $this->reason = $reason;
        $this->removeExternals = $removeExternals;
    }

    /**
     * Determines if external resources must be removed as well.
     */
    public function doRemoveExternals(): bool
    {
        return $this->removeExternals;
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
    public function getSenderId(): ?int
    {
        return $this->senderId;
    }

    /**
     * Get the void reason.
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }
}
