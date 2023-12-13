<?php

declare(strict_types=1);

namespace App\Envelope\Message;

final class SignEnvelopeMessage
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
     * The list if temporary files.
     */
    private array $temporaryFiles;

    public function __construct(?int $envelopeId, int $senderId, array $temporaryFiles)
    {
        $this->senderId = $senderId;
        $this->envelopeId = $envelopeId;
        $this->temporaryFiles = $temporaryFiles;
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
     * Get the list if temporary files.
     */
    public function getTemporaryFiles(): array
    {
        return $this->temporaryFiles;
    }
}
