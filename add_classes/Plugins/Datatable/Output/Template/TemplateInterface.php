<?php

declare(strict_types=1);

namespace App\Plugins\Datatable\Output\Template;

use App\Plugins\Datatable\Output\AttributesAwareInterface;
use App\Plugins\Datatable\Output\RenderInterface;
use ArrayAccess;
use Stringable;

interface TemplateInterface extends Stringable, RenderInterface, AttributesAwareInterface
{
    /**
     * Creates a new template based on this.
     *
     * @param array|ArrayAccess $attributes
     */
    public function fork($attributes = []): self;
}
