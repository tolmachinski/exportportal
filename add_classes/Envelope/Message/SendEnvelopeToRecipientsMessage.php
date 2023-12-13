<?php

declare(strict_types=1);

namespace App\Envelope\Message;

final class SendEnvelopeToRecipientsMessage
{
    use AccessRulesAwareTrait;

    /**
     * The envelope ID.
     */
    private int $envelopeId;

    /**
     * The user ID.
     */
    private ?int $userId;

    /**
     * The list of the recipients.
     */
    private array $recipients;

    /**
     * The flag that indicates if the notifications are enabled.
     */
    private bool $enableNotifications;

    public function __construct(int $envelopeId, ?int $userId = null, array $recipients = [], bool $enableNotifications = true)
    {
        $this->userId = $userId;
        $this->envelopeId = $envelopeId;
        $this->recipients = $recipients;
        $this->enableNotifications = $enableNotifications;
    }

    /**
     * Determines if all communication must be silenced.
     */
    public function isSilent(): bool
    {
        return false === $this->enableNotifications;
    }

    /**
     * Get the envelope ID.
     */
    public function getEnvelopeId(): ?int
    {
        return $this->envelopeId;
    }

    /**
     * Get the list of the recipients.
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }

    /**
     * Get the user ID.
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }
}
