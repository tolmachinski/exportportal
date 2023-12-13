<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Order\Command;

use App\Common\Contracts\Notifier\SystemChannel;
use App\Envelope\Bridge\Notifier\Notification\OrderDocumentEnvelopeNotification;
use App\Envelope\Bridge\Notifier\Type;
use App\Envelope\Bridge\Order\Event\BindEnvelopeAndOrder;
use App\Envelope\Bridge\Order\Message\BindEnvelopeAndOrderMessage;
use App\Envelope\Bridge\Order\Message\CreateOrderEnvelopeDraftMessage;
use App\Envelope\Command\CreateEnvelopeDraft;
use App\Envelope\Exception\WriteEnvelopeException;
use ExportPortal\Bridge\Notifier\Recipient\RightfulRecipient;
use InvalidArgumentException;

class CreateOrderEnvelopeDraft extends CreateEnvelopeDraft
{
    /**
     * {@inheritdoc}
     *
     * @throws WriteEnvelopeException if failed to write the envelope draft
     */
    public function __invoke(CreateOrderEnvelopeDraftMessage $message)
    {
        $envelopeId = parent::__invoke($message);

        // Bind envelope to the order
        (new BindEnvelopeAndOrder($this->envelopesRepository->getRelation('orderReference')->getRelated()))->__invoke(
            new BindEnvelopeAndOrderMessage((int) $envelopeId, $message->getOrderId())
        );

        return $envelopeId;
    }

    /**
     * Sends the notifications for this command.
     *
     * @param CreateOrderEnvelopeDraftMessage $message
     */
    protected function sendNotifications(int $envelopeId, ?string $envelopeTitle, $message): void
    {
        if (!$message instanceof CreateOrderEnvelopeDraftMessage) {
            throw new InvalidArgumentException(\sprintf('The message must be instance of %s', CreateOrderEnvelopeDraftMessage::class));
        }
        if (empty($accessRules = $message->getAccessRulesList())) {
            return;
        }

        $this->notifier->send(
            new OrderDocumentEnvelopeNotification(
                Type::CREATED_ENVELOPE_FOR_MANAGER,
                $message->getOrderId(),
                $envelopeId,
                $envelopeTitle,
                $this->getNotificationSender($envelopeId, $message->getSenderId()),
                [(string) SystemChannel::STORAGE()]
            ),
            new RightfulRecipient($accessRules)
        );
    }
}
