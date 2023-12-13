<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Order\Message;

/**
 * Message that is send to bind the envelope and order.
 */
final class BindEnvelopeAndOrderMessage
{
    /**
     * The envelope ID.
     */
    private int $envelopeId;

    /**
     * The order ID.
     */
    private int $orderId;

    public function __construct(int $envelopeId, int $orderId)
    {
        $this->orderId = $orderId;
        $this->envelopeId = $envelopeId;
    }

    /**
     * Get the envelope ID.
     */
    public function getEnvelopeId(): int
    {
        return $this->envelopeId;
    }

    /**
     * Get the order ID.
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }
}
