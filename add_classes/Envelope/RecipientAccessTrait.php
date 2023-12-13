<?php

declare(strict_types=1);

namespace App\Envelope;

use App\Common\Exceptions\AccessDeniedException;
use Doctrine\Common\Collections\Collection;

trait RecipientAccessTrait
{
    /**
     * Asserts if the sender is recipient from the current routing.
     *
     * @throws AccessDeniedException if sender is not a part of the current routing
     */
    private function assertSenderIsCurrentRecipient(int $senderId, Collection $currentRouting): void
    {
        if (!$currentRouting->exists(fn ($index, array $recipient) => $senderId === (int) $recipient['id_user'])) {
            throw new AccessDeniedException('The user must be a recipient in the current routing order.');
        }
    }

    /**
     * Asserts if the current recipient is signer.
     *
     * @throws AccessDeniedException if current recipient is not a signer
     */
    private function assertRecipientIsSigner(array $currentRecipient): void
    {
        if (RecipientTypes::SIGNER !== $currentRecipient['type']) {
            throw new AccessDeniedException('The recipient must be a signer to decline signing.');
        }
    }

    /**
     * Asserts if the current recipient can sign or decline envelope.
     *
     * @throws AccessDeniedException if recipient cannot sign or decline envelope
     */
    private function assertRecipientCanSignAndDecline(array $currentRecipient): void
    {
        $this->assertRecipientIsSigner($currentRecipient);

        if (!\in_array($currentRecipient['status'], [RecipientStatuses::SENT, RecipientStatuses::DELIVERED])) {
            throw new AccessDeniedException('The recipient in this status cannot sign or decline envelope.');
        }
    }

    /**
     * Asserts if the current recipient can sign or decline envelope.
     *
     * @throws AccessDeniedException if recipient cannot sign or decline envelope
     */
    private function assertRecipientCanView(array $currentRecipient): void
    {
        if (RecipientTypes::VIEWER !== $currentRecipient['type']) {
            throw new AccessDeniedException('The recipient is not alowed to preview the envelope.');
        }

        if (!\in_array($currentRecipient['status'], [RecipientStatuses::SENT, RecipientStatuses::DELIVERED])) {
            throw new AccessDeniedException('The recipient in this status cannot sign or decline envelope.');
        }
    }
}
