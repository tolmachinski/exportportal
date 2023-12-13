<?php

declare(strict_types=1);

namespace App\Plugins\Datatable\Output\Badge;

use App\Plugins\Datatable\Output\Element\Element;
use App\Plugins\Datatable\Output\Template\TemplateInterface;

abstract class Badge extends Element
{
    /**
     * Creates instance of the badge.
     */
    public function __construct(TemplateInterface $template, string $text, string $color = null)
    {
        parent::__construct($template->withAttributes([
            'text'  => $text,
            'color' => $color ?? '',
        ]));
    }
}
