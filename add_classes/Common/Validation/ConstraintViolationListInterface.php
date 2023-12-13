<?php

namespace App\Common\Validation;

interface ConstraintViolationListInterface extends \IteratorAggregate, \Countable, \ArrayAccess
{
    /**
     * Adds a constraint violation to this list.
     */
    public function add(ConstraintViolationInterface $violation);

    /**
     * Returns the violation at a given offset.
     *
     * @param int $offset
     *
     * @throws \OutOfBoundsException if the offset does not exist
     *
     * @return ConstraintViolationInterface
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
     * Merges an existing violation list into this list.
     */
    public function merge(ConstraintViolationListInterface $violationsList);

    /**
     * Sets a violation at a given offset.
     *
     * @param int $offset
     */
    public function set($offset, ConstraintViolationInterface $violation);

    /**
     * Removes a violation at a given offset.
     *
     * @param int $offset
     */
    public function remove($offset);

    /**
     * Clears the list of constriant violations.
     */
    public function clear(): void;
}
