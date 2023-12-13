<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\DigitalSignature\Message;

final class OpenEnvelopeEditModeMessage
{
    /**
     * The envelope ID.
     */
    private ?int $envelopeId;

    /**
     * The final redirect URL.
     */
    private ?string $redirectUrl;

    public function __construct(?int $envelopeId, ?string $redirectUrl)
    {
        $this->envelopeId = $envelopeId;
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * Get the envelope ID.
     */
    public function getEnvelopeId(): ?int
    {
        return $this->envelopeId;
    }

    /**
     * Get the final redirect URL.
     */
    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }
}
