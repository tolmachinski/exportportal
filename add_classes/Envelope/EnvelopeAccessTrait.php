<?php

declare(strict_types=1);

namespace App\Envelope;

use App\Common\Exceptions\AccessDeniedException;
use App\Common\Exceptions\OwnershipException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

trait EnvelopeAccessTrait
{
    /**
     * Check if sender has access to the order.
     *
     * @throws OwnershipException if sender is not the owner of the envelope
     */
    private function assertSenderIsEnvelopeOwner(int $senderId, array $envelope): void
    {
        if ($senderId !== $envelope['id_sender']) {
            throw new OwnershipException('Only sender can edit this envelope.');
        }
    }

    /**
     * Asserts that user can view envelope.
     */
    private function assertCanViewEnvelopeDetails(int $userId, array $envelope): void
    {
        if (
            $userId !== $envelope['id_sender']
            && empty($envelope['recipients'] || $envelope['recipients']->isEmpty())
            && !$envelope['recipients']->exists(fn ($i, array $recipient) => $userId === $recipient['id_user'])
        ) {
            throw new AccessDeniedException('Only sender or recipient can view details of this envelope.');
        }
    }

    /**
     * Check if envelope is active.
     *
     * @throws AccessDeniedException if envelope is not active
     */
    private function assertEnvelopeIsActive(array $envelope): void
    {
        if (in_array($envelope['status'], [
            EnvelopeStatuses::CREATED,
            EnvelopeStatuses::DECLINED,
            EnvelopeStatuses::COMPLETED,
            EnvelopeStatuses::VOIDED,
        ])) {
            throw new AccessDeniedException('The envelope must be active.');
        }
    }

    /**
     * Check if sender has access to the order.
     */
    private function assertCanSignOrDeclineEnvelope(int $userId, array $envelope): void
    {
        $this->assertEnvelopeIsActive($envelope);

        $currentRecipient = $envelope['recipients_routing']['current_routing'][$userId] ?? null;
        if (null === $currentRecipient) {
            throw new AccessDeniedException('The recipient must be a part of current routing process.');
        }

        if (RecipientTypes::SIGNER !== $currentRecipient['type']) {
            throw new AccessDeniedException('The recipient must be a signer to sign or decline the envelopes.');
        }

        if (!in_array($currentRecipient['status'], [RecipientStatuses::SENT, RecipientStatuses::DELIVERED])) {
            throw new AccessDeniedException(sprintf(
                'Only recipients in status %s and %s can sign or decline the envelope.',
                RecipientStatuses::SENT,
                RecipientStatuses::DELIVERED
            ));
        }
    }

    /**
     * Check if sender has access to the order.
     */
    private function assertCanViewEnvelope(int $userId, array $envelope): void
    {
        $this->assertEnvelopeIsActive($envelope);

        $currentRecipient = $envelope['recipients_routing']['current_routing'][$userId] ?? null;
        if (null === $currentRecipient) {
            throw new AccessDeniedException('The recipient must be a part of current routing process.');
        }

        if (RecipientTypes::VIEWER !== $currentRecipient['type']) {
            throw new AccessDeniedException('The recipient must be a signer to sign or decline the envelopes.');
        }

        if (!in_array($currentRecipient['status'], [RecipientStatuses::SENT, RecipientStatuses::DELIVERED])) {
            throw new AccessDeniedException(sprintf(
                'Only recipients in status %s and %s can sign or decline the envelope.',
                RecipientStatuses::SENT,
                RecipientStatuses::DELIVERED
            ));
        }
    }

    /**
     * Check if sender has access to the order.
     *
     * @throws OwnershipException    if sender is not the owner of the envelope
     * @throws AccessDeniedException if envelope is not active
     * @throws AccessDeniedException if envelope is not eligible for declining porcess
     * @throws AccessDeniedException if last recipient didn't signed envelope
     */
    private function assertCanDeclineOrConfirmSignedEnvelope(int $userId, array $envelope): void
    {
        $this->assertEnvelopeIsActive($envelope);
        $this->assertSenderIsEnvelopeOwner($userId, $envelope);

        $signableType = [RecipientTypes::SIGNER];
        /** @var null|Collection $currentRouting */
        $currentRouting = $envelope['recipients_routing']['current_routing'] ?? new ArrayCollection();
        $waitingRecipient = $currentRouting->filter(fn (array $recipient) => \in_array($recipient['type'], $signableType))->first() ?: null;
        if ($currentRouting->isEmpty() || $currentRouting->count() > 1 || empty($waitingRecipient)) {
            throw new AccessDeniedException('This envelope is not eligible to the this confirming/declining process.');
        }

        if (RecipientStatuses::SIGNED !== $waitingRecipient['status']) {
            throw new AccessDeniedException('The envelope must be signed first');
        }
    }

    /**
     * Check if sender has access to the order.
     *
     * @throws AccessDeniedException if envelope is not of the type "personal" or "draft"
     * @throws AccessDeniedException if envelope is not in the status "created"
     */
    private function assertEnvelopeIsEditable(array $envelope): void
    {
        if (!in_array($envelope['type'], [EnvelopeTypes::PERSONAL])) {
            throw new AccessDeniedException(
                sprintf(
                    'Only the envelopes of the type %s can be edited.',
                    EnvelopeTypes::PERSONAL
                )
            );
        }

        if (EnvelopeStatuses::CREATED !== $envelope['status']) {
            throw new AccessDeniedException('Only the envelopes in the status "created" can be edited.');
        }
    }

    /**
     * Check if sender has access to the order.
     *
     * @throws AccessDeniedException if envelope is not in the status "created"
     */
    private function assertEnvelopeIsEditableAdmin(array $envelope): void
    {
        if (in_array($envelope['status'], [
            EnvelopeStatuses::DECLINED,
            EnvelopeStatuses::COMPLETED,
            EnvelopeStatuses::VOIDED,
        ])) {
            throw new AccessDeniedException('Only the envelopes that are not completed can be edited.');
        }
    }

    /**
     * Check if envelope is voidable.
     *
     * @throws AccessDeniedException if envelope is already voided
     */
    private function assertEnvelopeIsVoidable(array $envelope): void
    {
        if (EnvelopeStatuses::VOIDED === $envelope['status']) {
            throw new AccessDeniedException('The envelope is already voided.');
        }
    }

    /**
     * Asserts if envelope description can be edited.
     */
    private function assertCanEditEnvelopeDisplayInfo(array $envelope): void
    {
        if (!in_array($envelope['type'], [EnvelopeTypes::PERSONAL])) {
            throw new AccessDeniedException(
                sprintf(
                    'Only the envelopes of the type %s can be edited.',
                    EnvelopeTypes::PERSONAL
                )
            );
        }

        if (!$this->isActiveEnvelope($envelope)) {
            throw new AccessDeniedException('The description can be edited only if envelope is active.');
        }
    }

    /**
     * Determines if envelope is active.
     */
    private function isActiveEnvelope(array $envelope): bool
    {
        return !in_array($envelope['status'], [
            EnvelopeStatuses::CREATED,
            EnvelopeStatuses::DECLINED,
            EnvelopeStatuses::COMPLETED,
            EnvelopeStatuses::VOIDED,
        ]);
    }
}
