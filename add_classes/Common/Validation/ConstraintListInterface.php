<?php

namespace App\Common\Validation;

interface ConstraintListInterface extends \IteratorAggregate, \Countable, \ArrayAccess
{
    /**
     * Adds a constraint to this list.
     */
    public function add(ConstraintInterface $constraint);

    /**
     * Returns the constraint at a given offset.
     *
     * @param int $offset
     *
     * @throws \OutOfBoundsException if the offset does not exist
     *
     * @return ConstraintInterface
     */
    public function get($offset);

    /**
     * Returns whether the given offset exists.
     *
     * @param int $offset
     *
     * @return bool
     */
    public function has($offset);

    /**
     * Merges an existing constraint list into this list.
     */
    public function merge(self $constraintsList);

    /**
     * Sets a constraint at a given offset.
     *
     * @param int $offset
     */
    public function set($offset, ConstraintInterface $constraint);

    /**
     * Removes a constraint at a given offset.
     *
     * @param int $offset
     */
    public function remove($offset);

    /**
     * Clears the list of constriants.
     */
    public function clear(): void;
}
