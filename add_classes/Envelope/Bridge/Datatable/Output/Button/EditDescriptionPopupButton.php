<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Datatable\Output\Button;

use App\Envelope\EnvelopeStatuses;
use App\Plugins\Datatable\Output\Button\PopupButton;
use App\Plugins\Datatable\Output\Template\TemplateInterface;

final class EditDescriptionPopupButton extends PopupButton
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
        if (
            (int) $envelope['id_sender'] !== $this->userId
            || in_array($envelope['status'], [...EnvelopeStatuses::PENDING, ...EnvelopeStatuses::FINISHED])
        ) {
            return false;
        }

        return true;
    }
}
