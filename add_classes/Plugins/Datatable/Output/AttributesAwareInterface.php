<?php

declare(strict_types=1);

namespace App\Plugins\Datatable\Output;

use ArrayAccess;

interface AttributesAwareInterface
{
    /**
     * Returns the attribute value by its name.
     *
     * @return null|mixed
     */
    public function getAttribute(string $name);

    /**
     * Returns all attributes.
     *
     * @return array|ArrayAccess
     */
    public function getAttributes();

    /**
     * Returns new instance with provided attributes.
     *
     * @param array|ArrayAccess $attributes
     */
    public function withAttributes($attributes): self;

    /**
     * Clears the attributes.
     */
    public function clearAttributes(): self;
}
