<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Datatable\Output\Badge;

use App\Envelope\EnvelopeStatuses;
use App\Envelope\RecipientStatuses;
use App\Envelope\RecipientTypes;
use App\Plugins\Datatable\Output\Badge\Badge;
use App\Plugins\Datatable\Output\Template\TemplateInterface;

final class SignedBadge extends Badge
{
    /**
     * The current user ID.
     */
    private int $userId;

    /**
     * {@inheritdoc}
     */
    public function __construct(int $userId, TemplateInterface $template, string $text, string $color = null)
    {
        parent::__construct($template, $text, $color);

        $this->userId = $userId;
    }

    /**
     * {@inheritdoc}
     */
    public function acceptsRow(array $envelope): bool
    {
        // Fail if envelope is not signable
        if (!$envelope['signing_enabled']) {
            return false;
        }

        // Fail if everyone signed the envelope
        if (EnvelopeStatuses::SIGNED === $envelope['status']) {
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
            && RecipientStatuses::SIGNED === $currentRecipient['status']
        ;
    }
}
