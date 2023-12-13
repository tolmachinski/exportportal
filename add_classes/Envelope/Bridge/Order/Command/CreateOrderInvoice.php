<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Order\Command;

use App\Envelope\Bridge\Order\Message\CreateOrderInvoiceMessage;
use App\Envelope\Event\RemoveEnvelope;
use App\Envelope\Exception\WriteEnvelopeException;
use App\Envelope\Exception\WriteRecipientException;
use App\Envelope\HistoryEvent;
use App\Envelope\Message\RemoveEnvelopeMessage;
use App\Envelope\RecipientTypes;
use Doctrine\Common\Collections\ArrayCollection;
use Throwable;

final class CreateOrderInvoice extends CreateOrderEnvelope
{
    /**
     * {@inheritdoc}
     *
     * @throws WriteEnvelopeException if failed to write the envelope draft
     */
    public function __invoke(CreateOrderInvoiceMessage $message)
    {
        $envelopeId = $this->createEnvelope(
            null,
            $message->getTitle(),
            $message->getType(),
            $message->getDescription(),
            null,
            $message->isDigital(),
            false
        );

        //region Finalize
        try {
            //region Recipients
            // Create evnelope recipients
            $recipientsList = \array_filter(
                [
                    ['type' => RecipientTypes::VIEWER, 'order' => 1, 'assignee' => $message->getBuyerId(), 'completed' => true],
                    ['type' => RecipientTypes::VIEWER, 'order' => 2, 'assignee' => $message->getSellerId(), 'completed' => true],
                ],
                fn (array $recipient) => null !== $recipient['assignee']
            );
            if (empty($recipientsList)) {
                throw new WriteRecipientException('The recipients list cannot be empty');
            }

            $this->finalizeEnvelope(
                $envelopeId,
                $message->getOrderId(),
                $message->getGenericUserId(),
                $recipientsList,
                $message->getInvoiceName(),
                true
            );

            //region Notify
            $this->sendNotifications(
                $message->getOrderId(),
                $envelopeId,
                $message->getTitle(),
                (new ArrayCollection($recipientsList))->map(fn (array $recipient) => $recipient['assignee']),
                $message->getAccessRulesList()
            );
            //endregion Notify

            // Write history
            $this->addHistoryEvent(new HistoryEvent(HistoryEvent::CREATE_INVOICE), $envelopeId, null, ['isInternal' => true]);
        } catch (Throwable $e) {
            // Remove envelope
            (new RemoveEnvelope($this->envelopesRepository, $this->fileStorage))->__invoke(new RemoveEnvelopeMessage($envelopeId));

            // ...and roll exception forward
            throw $e;
        }
        //endregion Finalize
    }
}
