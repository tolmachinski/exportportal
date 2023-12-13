<?php

declare(strict_types=1);

namespace App\Envelope\Message;

final class AccessDocumentMessage
{
    /**
     * The envelope ID.
     */
    private ?int $envelopeId;

    /**
     * The sender ID.
     */
    private int $senderId;

    /**
     * The document ID.
     */
    private ?int $documentId;

    /**
     * The TTL for access token.
     */
    private int $ttl;

    public function __construct(?int $envelopeId, int $senderId, ?int $documentId, int $ttl = 90)
    {
        $this->ttl = $ttl;
        $this->senderId = $senderId;
        $this->envelopeId = $envelopeId;
        $this->documentId = $documentId;
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
     * Get the document ID.
     */
    public function getDocumentId(): ?int
    {
        return $this->documentId;
    }

    /**
     * Get the TTL for access token.
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }
}
