<?php

namespace App\Documents\Versioning;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Traversable;

final class ContentContext implements ArrayAccess, IteratorAggregate, Countable
{
    /**
     * The context.
     *
     * @var array
     */
    private $context = array();

    /**
     * Creates the instance of the content context.
     */
    public function __construct(array $context = array())
    {
        $this->context = $context;
    }

    /**
     * Determines if the key exists in the context.
     */
    public function has(string $key): bool
    {
        return isset($this->context[$key]) || array_key_exists($key, $this->context);
    }

    /**
     * Get the context value by the key.
     */
    public function get(string $key)
    {
        return $this->context[$key] ?? null;
    }

    /**
     * Sets the value into the context.
     *
     * @param mixed $value
     */
    public function set(string $key, $value): self
    {
        $this->context[$key] = $value ?? null;

        return $this;
    }

    /**
     * Removes the value from context.
     */
    public function remove(string $key): self
    {
        if (isset($this->context[$key]) || array_key_exists($key, $this->context)) {
            $this->context[$key] = null;
            unset($this->context[$key]);
        }

        return $this;
    }

    /**
     * Clears the context.
     */
    public function clear(): self
    {
        $this->context = array();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return count($this->context);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        $this->remove($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): Traversable
    {
        yield from $this->context;
    }
}
