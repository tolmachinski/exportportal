<?php

declare(strict_types=1);

namespace App\Plugins\Datatable\Output\Element;

use App\Plugins\Datatable\Output\RenderInterface;

interface ElementListInterface extends RenderInterface
{
    /**
     * Determines if the element supports the current row.
     */
    public function acceptsRow(array $row): self;

    /**
     * Merge items from list and items of given lists into a new one.
     *
     * @param ElementListInterface<T> ...$lists The lists to merge.
     *
     * @throws ListMismatchException when trying to merge lists of different types
     *
     * @return ElementListInterface<T>
     */
    public function merge(ElementListInterface ...$lists): ElementListInterface;

    /**
     * Returns a native PHP array representation of this list.
     */
    public function toArray(): array;
}
