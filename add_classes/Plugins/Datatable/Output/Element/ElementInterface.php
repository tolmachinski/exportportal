<?php

declare(strict_types=1);

namespace App\Plugins\Datatable\Output\Element;

use App\Plugins\Datatable\Output\RenderInterface;
use JsonSerializable;
use Stringable;

interface ElementInterface extends RenderInterface, TemplateAwareInterface, Stringable, JsonSerializable
{
    /**
     * Determines if the element supports the current row.
     */
    public function acceptsRow(array $row): bool;
}
