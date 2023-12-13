<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Datatable\Output\Button;

use App\Plugins\Datatable\Output\Button\PopupButton;
use App\Plugins\Datatable\Output\Template\TemplateInterface;

final class CopyOriginalPopupButton extends PopupButton
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
        return (int) $envelope['id_sender'] === $this->userId;
    }
}
