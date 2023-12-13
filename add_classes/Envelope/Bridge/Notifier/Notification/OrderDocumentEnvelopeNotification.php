<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Notifier\Notification;

use App\Envelope\Bridge\Notifier\Sender;

class OrderDocumentEnvelopeNotification extends EnvelopeNotification
{
    /**
     * The order Id.
     */
    private ?int $orderId;

    /**
     * Creates instance of the notification.
     */
    public function __construct(
        string $subject,
        ?int $orderId = null,
        int $envelopeId,
        ?string $envelopeTitle = null,
        ?Sender $sender = null,
        ?array $channels = null
    ) {
        parent::__construct($subject, $envelopeId, $envelopeTitle, $sender, $channels);

        $this->orderId = $orderId;
    }

    /**
     * Prepares the replacement options for the message.
     */
    protected function prepareReplacementOptions(): array
    {
        return \array_merge(
            parent::prepareReplacementOptions(),
            [
                '[ORDER]'               => null !== $this->orderId ? \orderNumber($this->orderId) : '',
                '[ORDER_ID]'            => $this->orderId ?? '',
                '[ORDER_URL]'           => null !== $this->orderId ? \getUrlForGroup("/order/my/order_number/{$this->orderId}") : '',
                '[ORDERS_URL]'          => \getUrlForGroup('/order/my'),
                '[ADMIN_ORDERS_URL]'    => \getUrlForGroup('/order/all'),
                '[DOCUMENTS_URL]'       => \getUrlForGroup('/order_documents'),
                '[ADMIN_DOCUMENTS_URL]' => \getUrlForGroup('/order_documents/administration'),
            ]
        );
    }
}
