<?php

declare(strict_types=1);

namespace App\Envelope\Message;

final class StartRoutingOrderMessage
{
    /**
     * The envelope ID.
     */
    private int $envelopeId;

    /**
     * The next routing.
     */
    private ?int $routingOrder;

    /**
     * The last workflow step.
     */
    private ?int $lastWorkfllowStep;

    /**
     * The list of the recipients in the next routing.
     */
    private array $recipients;

    public function __construct(int $envelopeId, ?int $routingOrder = null, ?int $lastWorkfllowStep = null, array $recipients = [])
    {
        $this->envelopeId = $envelopeId;
        $this->recipients = $recipients;
        $this->routingOrder = $routingOrder;
        $this->lastWorkfllowStep = $lastWorkfllowStep;
    }

    /**
     * Get the envelope ID.
     */
    public function getEnvelopeId(): int
    {
        return $this->envelopeId;
    }

    /**
     * Get the routing order.
     */
    public function getRoutingOrder(): ?int
    {
        return $this->routingOrder;
    }

    /**
     * Get the last workflow step.
     */
    public function getLastWorkfllowStep(): ?int
    {
        return $this->lastWorkfllowStep;
    }

    /**
     * Get the list of the recipients in the next routing.
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }
}
