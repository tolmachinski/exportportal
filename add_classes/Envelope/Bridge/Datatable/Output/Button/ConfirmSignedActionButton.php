<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Datatable\Output\Button;

use App\Envelope\EnvelopeStatuses;
use App\Envelope\EnvelopeTypes;
use App\Envelope\RecipientStatuses;
use App\Envelope\RecipientTypes;
use App\Envelope\SigningMecahisms;
use App\Plugins\Datatable\Output\Button\ActionButton;
use App\Plugins\Datatable\Output\Template\TemplateInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class ConfirmSignedActionButton extends ActionButton
{
    /**
     * The current user ID.
     */
    private int $userId;

    /**
     * Creates instance of the popup button.
     */
    public function __construct(
        int $userId,
        TemplateInterface $template,
        string $text,
        ?string $title = null,
        ?string $className = null,
        ?string $icon = null,
        array $dataAttributes = []
    ) {
        parent::__construct($template, $text, $title, $className, $icon, $dataAttributes);

        $this->userId = $userId;
    }

    /**
     * {@inheritdoc}
     */
    public function acceptsRow(array $envelope): bool
    {
        // Fail if envelope is not signable or signing mechanism is not native
        if (!$envelope['signing_enabled'] || SigningMecahisms::NATIVE !== $envelope['signing_mechanism']) {
            return false;
        }

        // Internal envelopes cannot be confirmed
        if (EnvelopeTypes::INTERNAL === $envelope['type']) {
            return false;
        }

        // Fail if current user is not sender or envlope is not active
        if (
            (int) $envelope['id_sender'] !== $this->userId
            || in_array($envelope['status'], [...EnvelopeStatuses::PENDING, ...EnvelopeStatuses::FINISHED])
        ) {
            return false;
        }

        $confirmableTypes = [RecipientTypes::SIGNER];
        /** @var null|Collection $currentRouting */
        $currentRouting = $envelope['recipients_routing']['current_routing'] ?? new ArrayCollection();
        $waitingRecipient = $currentRouting->filter(fn (array $recipient) => \in_array($recipient['type'], $confirmableTypes))->first() ?: null;
        // Fail if routing is empty or not serial or there is no signers for current routing
        if ($currentRouting->isEmpty() || $currentRouting->count() > 1 || empty($waitingRecipient)) {
            return false;
        }

        return RecipientStatuses::SIGNED === $waitingRecipient['status'];
    }
}
