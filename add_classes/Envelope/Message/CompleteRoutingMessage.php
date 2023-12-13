<?php

declare(strict_types=1);

namespace App\Envelope\Message;

final class CompleteRoutingMessage
{
    use AccessRulesAwareTrait;

    /**
     * The flag that indicates if status ef the envelope must be changed.
     */
    public bool $changeStatus;
    /**
     * The envelope ID.
     */
    private int $envelopeId;

    /**
     * The last workflow step.
     */
    private ?int $lastWorkfllowStep;

    /**
     * The list of notifications recipients.
     */
    private array $notificationsRecipients;

    public function __construct(int $envelopeId, ?int $lastWorkfllowStep = null, bool $changeStatus = false, array $notificationsRecipients = [])
    {
        $this->envelopeId = $envelopeId;
        $this->changeStatus = $changeStatus;
        $this->lastWorkfllowStep = $lastWorkfllowStep;
        $this->notificationsRecipients = $notificationsRecipients;
    }

    /**
     * Determines if update of the envelope status must be omitted.
     */
    public function skipStatusUpdate(): bool
    {
        return !$this->changeStatus;
    }

    /**
     * Get the envelope ID.
     */
    public function getEnvelopeId(): ?int
    {
        return $this->envelopeId;
    }

    /**
     * Get the last workflow step.
     */
    public function getLastWorkfllowStep(): ?int
    {
        return $this->lastWorkfllowStep;
    }

    /**
     * Get the list of notifications assigneed.
     */
    public function getNotificationsRecipients(): array
    {
        return $this->notificationsRecipients;
    }
}
