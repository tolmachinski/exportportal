<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\DigitalSignature\Message;

final class OpenEnvelopeViewMessage
{
    /**
     * The envelope ID.
     */
    private ?int $envelopeId;

    /**
     * The user ID.
     */
    private int $userId;

    /**
     * The domain name.
     */
    private string $domain;

    /**
     * The return URL.
     */
    private string $returnUrl;

    public function __construct(?int $envelopeId, int $userId, string $domain, string $returnUrl)
    {
        $this->userId = $userId;
        $this->domain = $domain;
        $this->returnUrl = $returnUrl;
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
     * Get the user ID.
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * Get the domain name.
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Get the return URL.
     */
    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }
}
