<?php

declare(strict_types=1);

namespace App\Plugins\Datatable\Output\Element;

use App\Plugins\Datatable\Output\Template\TemplateInterface;

interface TemplateAwareInterface
{
    /**
     * Returns the template.
     */
    public function getTemplate(): TemplateInterface;

    /**
     * Returns new instance with provided template.
     */
    public function withTemplate(TemplateInterface $template): self;
}
