<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Datatable\Output\Button;

use App\Envelope\EnvelopeStatuses;
use App\Envelope\SigningMecahisms;
use App\Plugins\Datatable\Output\Button\ActionButton;
use App\Plugins\Datatable\Output\Template\TemplateInterface;

final class RequireApprovalButton extends ActionButton
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
        return (int) $envelope['id_sender'] === $this->userId
            && EnvelopeStatuses::CREATED === $envelope['status']
            && SigningMecahisms::NATIVE !== $envelope['signing_mechanism'];
    }
}
