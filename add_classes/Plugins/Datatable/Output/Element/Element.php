<?php

declare(strict_types=1);

namespace App\Plugins\Datatable\Output\Element;

use App\Plugins\Datatable\Output\Template\TemplateInterface;

class Element implements ElementInterface
{
    /**
     * The element template.
     */
    private TemplateInterface $template;

    /**
     * Creates instance of the element.
     */
    public function __construct(TemplateInterface $template)
    {
        $this->template = $template;
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
    public function render(): string
    {
        return $this->template->render();
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate(): TemplateInterface
    {
        return $this->template;
    }

    /**
     * {@inheritdoc}
     */
    public function withTemplate(TemplateInterface $template): self
    {
        $instance = clone $this;
        $instance->template = $template;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function acceptsRow(array $row): bool
    {
        return  true;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->render();
    }
}
