<?php

declare(strict_types=1);

namespace App\Envelope\Message;

final class CreateDigitalDraftMessage
{
    /**
     * The envelope ID.
     */
    private int $envelopeId;

    /**
     * @param int $envelopeId the envelope ID
     */
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
