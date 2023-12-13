<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Datatable\Output\Badge;

use App\Envelope\EnvelopeStatuses;
use App\Envelope\RecipientStatuses;
use App\Envelope\RecipientTypes;
use App\Plugins\Datatable\Output\Badge\Badge;
use App\Plugins\Datatable\Output\Template\TemplateInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class NeedConfirmation extends Badge
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
        // Fail if current user is not sender or envlope is not active
        if (in_array($envelope['status'], [...EnvelopeStatuses::PENDING, ...EnvelopeStatuses::FINISHED])) {
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
