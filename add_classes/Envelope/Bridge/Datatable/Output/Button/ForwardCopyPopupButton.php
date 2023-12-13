<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Datatable\Output\Button;

use App\Envelope\EnvelopeStatuses;
use App\Envelope\RecipientStatuses;
use App\Envelope\RecipientTypes;
use App\Plugins\Datatable\Output\Button\PopupButton;
use App\Plugins\Datatable\Output\Template\TemplateInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class ForwardCopyPopupButton extends PopupButton
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
        string $url,
        string $text,
        string $popupTitle,
        ?string $title = null,
        ?string $className = null,
        ?string $icon = null,
        array $dataAttributes = []
    ) {
        parent::__construct($template, $url, $text, $popupTitle, $title, $className, $icon, $dataAttributes);

        $this->userId = $userId;
    }

    /**
     * {@inheritdoc}
     */
    public function acceptsRow(array $envelope): bool
    {
        // Fail if user is not sender or envelope has invalid status (just created or voided)
        if (
            (int) $envelope['id_sender'] !== $this->userId
            || \in_array($envelope['status'], [EnvelopeStatuses::CREATED, EnvelopeStatuses::VOIDED])
        ) {
            return false;
        }

        /** @var null|Collection $documents */
        $documents = $envelope['documents'] ?? null;
        // Pass if there is no documents to copy - sender can add new ones in the form
        if (null === $documents) {
            return true;
        }

        $recipientsRouting = $envelope['recipients_routing'] ?? [];
        /** @var Collection $routingPipeline */
        $routingPipeline = $recipientsRouting['routing'] ?? new ArrayCollection(); // Get all routing for envelope
        if (!$routingPipeline->forAll(fn ($i, Collection $step) => 1 === $step->count())) {
            return false;
        }

        /** @var null|Collection $previousRouting */
        $previousRouting = $envelope['recipients_routing']['previous_routing'] ?? null;
        // If previous routing step exists we can safelly assume that we can copy the document
        // because we are not at the begining of the routing
        if (null !== $previousRouting) {
            return true;
        }

        // If we reached this place, that means, that it is the first routing
        // and that means that we need to check only for signers
        /** @var null|Collection $currentRouting */
        $currentRouting = $envelope['recipients_routing']['current_routing'] ?? null;
        if (null === $currentRouting) {
            return false;
        }

        $currentRecipient = $currentRouting->first() ?: null;
        // Fail if there is no recipient
        if (null === $currentRecipient) {
            return false;
        }

        // On the first step we don't need this button if recipient is not signer or the signed document was confirmed.
        return RecipientTypes::SIGNER !== $currentRecipient['type'] || RecipientStatuses::COMPLETED === $currentRecipient['status'];
    }
}
