<?php

declare(strict_types=1);

namespace App\Plugins\Datatable\Output\Template;

use ArrayAccess;
use InvalidArgumentException;

abstract class AbstractTemplate implements TemplateInterface
{
    /**
     * The attributes attached to the element.
     */
    private array $attributes;

    /**
     * Creates instance of the template.
     *
     * @param mixed $attributes
     */
    public function __construct($attributes = [])
    {
        $this->acceptsAttributes($attributes);
        $this->attributes = $attributes;
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
    public function getAttribute(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function withAttributes($attributes): self
    {
        $this->acceptsAttributes($attributes);
        $instance = clone $this;
        $instance->attributes = $attributes;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function clearAttributes(): self
    {
        $this->attributes = [];

        return $this;
    }

    /**
     * Creates a new template based on this.
     *
     * @param array|ArrayAccess $attributes
     */
    public function fork($attributes = []): self
    {
        $fork = clone $this;
        $fork->attributes = $attributes;

        return $fork;
    }

    /**
     * Dermines if attributes value is acceptable.
     *
     * @param mixed $attributes
     */
    private function acceptsAttributes($attributes): void
    {
        if (!\is_array($attributes) && !($attributes instanceof ArrayAccess)) {
            throw new InvalidArgumentException('The attributes parameter must be either an array or instance of ArrayAccess.');
        }
    }
}
