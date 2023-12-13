<?php

declare(strict_types=1);

namespace App\Plugins\Datatable\Output\Template;

final class NullTemplate extends AbstractTemplate
{
    /**
     * {@inheritdoc}
     */
    public function render(): string
    {
        return  '';
    }
}
