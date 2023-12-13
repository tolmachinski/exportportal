<?php

declare(strict_types=1);

namespace App\Plugins\Datatable\Output\Element\Decorate;

use App\Plugins\Datatable\Output\Element\ElementInterface;
use App\Plugins\Datatable\Output\Template\TemplateInterface;

/**
 * Decorates over element.
 */
class Decorator implements ElementInterface, DecoratorInterface
{
    /**
     * The decorated element.
     */
    private ElementInterface $element;

    /**
     * Creates instance of the element.
     */
    public function __construct(ElementInterface $element)
    {
        $this->element = $element;
    }

    /**
     * Returns the rendered element.
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * {@inheritdoc}
     */
    public function getElement(): ElementInterface
    {
        return $this->element;
    }

    /**
     * {@inheritdoc}
     */
    public function render(): string
    {
        return $this->element->render();
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate(): TemplateInterface
    {
        return $this->element->getTemplate();
    }

    /**
     * {@inheritdoc}
     */
    public function withTemplate(TemplateInterface $template): self
    {
        $instance = clone $this;
        $instance->element = $this->element->withTemplate($template);

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function acceptsRow(array $row): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->render();
    }
}
