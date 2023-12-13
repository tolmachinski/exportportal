<?php

namespace App\Common\Validation;

interface ValidationDataInterface extends \IteratorAggregate, \Countable, \ArrayAccess
{
    /**
     * Adds a value to this set.
     *
     * @param mixed $value
     *
     * @return self
     */
    public function add($value);

    /**
     * Returns the value at a given offset.
     *
     * @param int|string $offset
     *
     * @throws \OutOfBoundsException if the offset does not exist
     *
     * @return mixed
     */
    public function get($offset);

    /**
     * Returns whether the given offset exists.
     *
     * @param int|string $offset
     *
     * @return bool
     */
    public function has($offset);

    /**
     * Merges an existing data set into this set.
     *
     * @param ValidationDataInterface $dataSet
     */
    public function merge(ValidationDataInterface $dataSet);

    /**
     * Sets a value at a given offset.
     *
     * @param int|string $offset
     * @param mixed      $value
     *
     * @return self
     */
    public function set($offset, $value);

    /**
     * Removes a value at a given offset.
     *
     * @param int|string $offset
     *
     * @return self
     */
    public function remove($offset);
}
