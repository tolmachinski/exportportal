<?php

declare(strict_types=1);

namespace App\Plugins\Datatable\Output\Button;

use App\Plugins\Datatable\Output\Element\Element;
use App\Plugins\Datatable\Output\Template\TemplateInterface;

class ActionButton extends Element
{
    /**
     * Creates instance of the action button.
     */
    public function __construct(
        TemplateInterface $template,
        string $text,
        ?string $title = null,
        ?string $className = null,
        ?string $icon = null,
        array $dataAttributes = []
    ) {
        parent::__construct($template->withAttributes([
            'text'  => $text,
            'icon'  => $icon ?? '',
            'title' => $title ?? '',
            'class' => $className ?? '',
            'data'  => $dataAttributes,
        ]));
    }
}
