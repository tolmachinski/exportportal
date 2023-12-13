<?php

declare(strict_types=1);

namespace App\Envelope\Message;

final class DeliverEnvelopeMessage
{
    /**
     * The envelope ID.
     */
    private int $envelopeId;

    /**
     * The sender ID.
     */
    private int $senderId;

    /**
     * The routing order.
     */
    private ?int $routingOrder;

    public function __construct(int $envelopeId, int $senderId, ?int $routingOrder = null)
    {
        $this->senderId = $senderId;
        $this->envelopeId = $envelopeId;
        $this->routingOrder = $routingOrder;
    }

    /**
     * Get the envelope ID.
     */
    public function getEnvelopeId(): int
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
     * Get the routing order.
     */
    public function getRoutingOrder(): ?int
    {
        return $this->routingOrder;
    }
}
