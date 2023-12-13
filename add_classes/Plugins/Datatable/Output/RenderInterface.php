<?php

declare(strict_types=1);

namespace App\Plugins\Datatable\Output;

interface RenderInterface
{
    /**
     * Renders the element.
     */
    public function render(): string;
}
