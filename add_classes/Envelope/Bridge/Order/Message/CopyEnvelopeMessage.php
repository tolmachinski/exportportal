<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Order\Message;

use App\Envelope\Message\CopyEnvelopeMessage as BaseCopyEnvelopeMessage;

/**
 * Message that is send to update the envelope draft.
 */
final class CopyEnvelopeMessage extends BaseCopyEnvelopeMessage
{
    /**
     * The order ID.
     */
    private int $orderId;

    public function __construct(
        int $orderId,
        ?int $envelopeId,
        int $senderId,
        string $title,
        string $type,
        string $description,
        string $signingMechanism,
        array $properties = [],
        array $recipients = [],
        array $copiedFiles = [],
        array $temporaryFiles = []
    ) {
        parent::__construct(
            $envelopeId,
            $senderId,
            $title,
            $type,
            $description,
            $signingMechanism,
            $properties,
            $recipients,
            $copiedFiles,
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
