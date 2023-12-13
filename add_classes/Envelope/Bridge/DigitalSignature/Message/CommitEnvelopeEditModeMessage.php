<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\DigitalSignature\Message;

final class CommitEnvelopeEditModeMessage
{
    /**
     * The original envelope UUID.
     */
    private ?string $originalEnvelopeUuid;

    public function __construct(?string $originalEnvelopeUuid)
    {
        $this->originalEnvelopeUuid = $originalEnvelopeUuid;
    }

    /**
     * Get the original envelope UUID.
     */
    public function getOriginalEnvelopeUuid(): ?string
    {
        return $this->originalEnvelopeUuid;
    }
}
