<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Notifier\Notification;

use App\Envelope\Bridge\Notifier\Sender;

final class OrderDocumentChildEnvelopeNotification extends OrderDocumentEnvelopeNotification
{
    /**
     * The parent envelope title.
     */
    protected ?string $parentEnvelopeTitle;
    /**
     * The parent envelope Id.
     */
    private ?int $parentEnvelopeId;

    /**
     * Creates instance of the notification.
     */
    public function __construct(
        string $subject,
        ?int $orderId = null,
        int $childEnvelopeId,
        ?string $childEnvelopeTitle = null,
        ?int $parentEnvelopeId = null,
        ?string $parentEnvelopeTitle = null,
        ?Sender $sender = null,
        ?array $channels = null
    ) {
        parent::__construct($subject, $orderId, $childEnvelopeId, $childEnvelopeTitle, $sender, $channels);

        $this->parentEnvelopeId = $parentEnvelopeId;
        $this->parentEnvelopeTitle = $parentEnvelopeTitle;
    }

    /**
     * Prepares the replacement options for the message.
     */
    protected function prepareReplacementOptions(): array
    {
        return \array_merge(
            parent::prepareReplacementOptions(),
            [
                '[PARENT_DOCUMENT]'       => \orderNumber($this->parentEnvelopeId),
                '[PARENT_DOCUMENT_ID]'    => $this->parentEnvelopeId,
                '[PARENT_DOCUMENT_TITLE]' => \cleanOutput($this->parentEnvelopeTitle ?? '') ?: null,
            ]
        );
    }
}
