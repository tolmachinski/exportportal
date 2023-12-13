<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Order\Message;

use App\Envelope\Message\CreateEnvelopeDraftMessage;

/**
 * Message that is send to create the envelope draft.
 */
final class CreateOrderEnvelopeDraftMessage extends CreateEnvelopeDraftMessage
{
    /**
     * The order ID.
     */
    private int $orderId;

    public function __construct(
        int $orderId,
        int $senderId,
        string $title,
        string $type,
        string $description,
        string $envelopeType,
        string $signingMechanism,
        array $properties = [],
        array $recipients = [],
        array $temporaryFiles = []
    ) {
        parent::__construct(
            $senderId,
            $title,
            $type,
            $description,
            $envelopeType,
            $signingMechanism,
            $properties,
            $recipients,
            $temporaryFiles
        );

        $this->orderId = $orderId;
    }

    /**
     * Get the order ID.
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }
}
