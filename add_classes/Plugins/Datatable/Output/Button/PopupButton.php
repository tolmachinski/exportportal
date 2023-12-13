<?php

declare(strict_types=1);

namespace App\Plugins\Datatable\Output\Button;

use App\Plugins\Datatable\Output\Element\Element;
use App\Plugins\Datatable\Output\Template\TemplateInterface;

class PopupButton extends Element
{
    /**
     * Creates instance of the popup button.
     */
    public function __construct(
        TemplateInterface $template,
        string $url,
        string $text,
        string $popupTitle,
        ?string $title = null,
        ?string $className = null,
        ?string $icon = null,
        array $dataAttributes = []
    ) {
        parent::__construct($template->withAttributes([
            'url'        => $url,
            'icon'       => $icon,
            'text'       => $text,
            'title'      => $title ?? '',
            'class'      => $className ?? '',
            'data'       => $dataAttributes,
            'popupTitle' => $popupTitle,
        ]));
    }
}
