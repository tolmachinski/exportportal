<?php

declare(strict_types=1);

namespace App\Plugins\Datatable\Output\Element\Decorate;

use App\Plugins\Datatable\Output\Element\ElementInterface;

interface DecoratorInterface
{
    /**
     * Gets the decorated element.
     */
    public function getElement(): ElementInterface;
}
