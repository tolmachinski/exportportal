<?php

declare(strict_types=1);

namespace App\Plugins\Datatable\Output\Element;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;
use Stringable;

class ElementList implements Stringable, ElementListInterface
{
    /**
     * The collection of the lements.
     */
    private Collection $elements;

    /**
     * Creates instance of the list.
     */
    public function __construct(array $elements = [])
    {
        foreach ($elements as $element) {
            if (!$element instanceof ElementInterface) {
                throw new InvalidArgumentException(
                    \sprintf('The elements in the list must be an instance of the %s', ElementInterface::class)
                );
            }
        }

        $this->elements = new ArrayCollection($elements);
    }

    /**
     * Returns the rendered list.
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * {@inheritdoc}
     */
    public function render(): string
    {
        return \implode('', $this->elements->map(fn (ElementInterface $e) => $e->render())->toArray());
    }

    /**
     * {@inheritdoc}
     */
    public function acceptsRow(array $row): self
    {
        return new static($this->elements->filter(
            fn (ElementInterface $e) => $e->acceptsRow($row))->toArray()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function merge(ElementListInterface ...$lists): ElementListInterface
    {
        $temp = [$this->elements->toArray()];

        foreach ($lists as $index => $list) {
            if (!$list instanceof static) {
                throw new ListMismatchException(
                    sprintf('List with index %d must be of type %s', $index, static::class)
                );
            }

            $temp[] = $list->toArray();
        }

        /** @var array<array-key, T> $merge */
        $merge = array_merge(...$temp);
        $list = clone $this;
        $list->elements = new ArrayCollection($merge);

        return $list;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return $this->elements->toArray();
    }
}
