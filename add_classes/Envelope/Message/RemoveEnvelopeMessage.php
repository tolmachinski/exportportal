<?php

declare(strict_types=1);

namespace App\Envelope\Message;

final class RemoveEnvelopeMessage
{
    /**
     * The envelope ID.
     */
    private int $envelopeId;

    public function __construct(int $envelopeId)
    {
        $this->envelopeId = $envelopeId;
    }

    /**
     * Get the envelope ID.
     */
    public function getEnvelopeId(): int
    {
        return $this->envelopeId;
    }
}
