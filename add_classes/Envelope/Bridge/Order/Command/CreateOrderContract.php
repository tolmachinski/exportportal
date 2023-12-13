<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Order\Command;

use App\Envelope\Bridge\Order\Message\CreateOrderContractMessage;
use App\Envelope\Event\RemoveEnvelope;
use App\Envelope\Exception\WriteEnvelopeException;
use App\Envelope\Exception\WriteRecipientException;
use App\Envelope\HistoryEvent;
use App\Envelope\Message\RemoveEnvelopeMessage;
use App\Envelope\RecipientTypes;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;
use Throwable;

final class CreateOrderContract extends CreateOrderEnvelope
{
    /**
     * {@inheritdoc}
     *
     * @throws WriteEnvelopeException if failed to write the envelope draft
     */
    public function __invoke(CreateOrderContractMessage $message)
    {
        if (null === $message->getSellerId() && null === $message->getBuyerId()) {
            throw new InvalidArgumentException('At least buyer or seller are required');
        }

        // Create evnelope recipients
        $now = new DateTimeImmutable();
        $recipientsList = \with(
            \array_filter(
                [
                    ['type' => RecipientTypes::SIGNER, 'order' => 1, 'assignee' => $message->getBuyerId()],
                    ['type' => RecipientTypes::SIGNER, 'order' => 2, 'assignee' => $message->getSellerId()],
                    ['type' => RecipientTypes::VIEWER, 'order' => 3, 'assignee' => $message->getShipperId()],
                ],
                fn (array $recipient) => null !== $recipient['assignee']
            ),
            fn (array $recipientsList) => \array_map(
                fn (int $index, array $recipient) => \array_merge($recipient, ['expiresAt' => $now->add(new DateInterval(\sprintf('P%sD', 3 * ($index + 1))))]),
                \array_keys($recipientsList),
                $recipientsList
            )
        );
        if (empty($recipientsList)) {
            throw new WriteRecipientException('The recipients list cannot be empty');
        }

        $envelopeId = $this->createEnvelope(
            null,
            $message->getTitle(),
            $message->getType(),
            $message->getDescription(),
            $now->add(new DateInterval(\sprintf('P%sD', 3 * \count($recipientsList)))),
            $message->isDigital(),
            true
        );

        //region Finalize
        try {
            $this->finalizeEnvelope(
                $envelopeId,
                $message->getOrderId(),
                $message->getGenericUserId(),
                $recipientsList,
                $message->getContractName()
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
            $this->addHistoryEvent(new HistoryEvent(HistoryEvent::CREATE_CONTRACT), $envelopeId, null, ['isInternal' => true]);
        } catch (Throwable $e) {
            // Remove envelope
            (new RemoveEnvelope($this->envelopesRepository, $this->fileStorage))->__invoke(new RemoveEnvelopeMessage($envelopeId));

            // ...and roll exception forward
            throw $e;
        }
        //endregion Finalize
    }
}
