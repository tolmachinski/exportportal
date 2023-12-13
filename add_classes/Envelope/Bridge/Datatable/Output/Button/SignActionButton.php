<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Datatable\Output\Button;

use App\Envelope\EnvelopeStatuses;
use App\Envelope\RecipientStatuses;
use App\Envelope\RecipientTypes;
use App\Envelope\SigningMecahisms;
use App\Plugins\Datatable\Output\Button\ActionButton;
use App\Plugins\Datatable\Output\Template\TemplateInterface;

final class SignActionButton extends ActionButton
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
        // Fail if envelope is not signable or signing mechanism is native
        if (!$envelope['signing_enabled'] || SigningMecahisms::NATIVE === $envelope['signing_mechanism']) {
            return false;
        }

        // Fail if current user is sender or envelope is not active
        if (in_array($envelope['status'], [...EnvelopeStatuses::PENDING, ...EnvelopeStatuses::FINISHED])) {
            return false;
        }
        $currentRecipient = $envelope['recipients_routing']['current_routing'][$this->userId] ?? null;

        return
            null !== $currentRecipient
            && RecipientTypes::SIGNER === $currentRecipient['type']
            && \in_array($currentRecipient['status'], [RecipientStatuses::SENT, RecipientStatuses::DELIVERED])
        ;
    }
}
