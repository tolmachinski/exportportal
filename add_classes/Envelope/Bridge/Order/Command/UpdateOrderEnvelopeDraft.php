<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Order\Command;

use App\Common\Contracts\Notifier\SystemChannel;
use App\Common\Exceptions\NotFoundException;
use App\Envelope\Bridge\Notifier\Notification\OrderDocumentEnvelopeNotification;
use App\Envelope\Bridge\Notifier\Sender;
use App\Envelope\Bridge\Notifier\Type;
use App\Envelope\Bridge\Order\Message\UpdateOrderEnvelopeDraftMessage;
use App\Envelope\Command\UpdateEnvelopeDraft;
use App\Envelope\Exception\WriteEnvelopeException;
use App\Envelope\Message\UpdateEnvelopeDraftMessage;
use ExportPortal\Bridge\Notifier\Recipient\RightfulRecipient;

class UpdateOrderEnvelopeDraft extends UpdateEnvelopeDraft
{
    /**
     * {@inheritdoc}
     *
     * @throws WriteEnvelopeException if failed to write the envelope draft
     */
    public function __invoke(UpdateOrderEnvelopeDraftMessage $message)
    {
        return parent::__invoke($message);
    }

    /**
     * {@inheritDoc}
     */
    protected function sendNotifications(int $envelopeId, array $envelope, UpdateEnvelopeDraftMessage $message, ?Sender $sender): void
    {
        if (empty($accessRules = $message->getAccessRulesList())) {
            return;
        }

        $this->notifier->send(
            new OrderDocumentEnvelopeNotification(Type::UPDATED_ENVELOPE_FOR_MANAGER, $envelope['order_reference']['id_order'], $envelopeId, $envelope['display_title'], $sender, [
                (string) SystemChannel::STORAGE(),
            ]),
            new RightfulRecipient($accessRules)
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function findEnvelopeInStorage(?int $envelopeId): array
    {
        if (
            null === $envelopeId
            || null === $envelope = $this->envelopesRepository->findOne($envelopeId, [
                'with' => ['extended_sender as sender', 'order_reference'],
            ])
        ) {
            throw new NotFoundException(sprintf('The envelope with ID %s is not found', varToString($envelopeId)));
        }

        return $envelope;
    }
}
