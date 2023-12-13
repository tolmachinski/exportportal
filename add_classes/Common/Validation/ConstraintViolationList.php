<?php

namespace App\Common\Validation;

class ConstraintViolationList implements ConstraintViolationListInterface
{
    /**
     * @var ConstraintViolationInterface[]
     */
    private $violations = [];

    /**
     * Creates a new constraint violation list.
     *
     * @param ConstraintViolationInterface[] $violations The constraint violations to add to the list
     */
    public function __construct(array $violations = [])
    {
        foreach ($violations as $violation) {
            $this->add($violation);
        }
    }

    /**
     * Converts the violation into a string for debugging purposes.
     *
     * @return string The violation as string
     */
    public function __toString()
    {
        $string = '';

        foreach ($this->violations as $violation) {
            $string .= $violation . "\n";
        }

        return $string;
    }

    /**
     * {@inheritdoc}
     */
    public function add(ConstraintViolationInterface $violation)
    {
        $this->violations[] = $violation;
    }

    /**
     * {@inheritdoc}
     */
    public function get($offset)
    {
        if (!isset($this->violations[$offset])) {
            throw new \OutOfBoundsException(sprintf('The offset "%s" does not exist.', $offset));
        }

        return $this->violations[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function has($offset)
    {
        return isset($this->violations[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function merge(ConstraintViolationListInterface $violationsList)
    {
        foreach ($violationsList as $violation) {
            $this->violations[] = $violation;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set($offset, ConstraintViolationInterface $violation)
    {
        $this->violations[$offset] = $violation;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($offset)
    {
        unset($this->violations[$offset]);
    }

    /**
     * Clears the list of constriant violations.
     */
    public function clear(): void
    {
        $this->violations = [];
    }

    /**
     * {@inheritdoc}
     *
     * @return \ArrayIterator|ConstraintViolationInterface[]
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->violations);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return \count($this->violations);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
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
    public function offsetSet($offset, $violation)
    {
        if (null === $offset) {
            $this->add($violation);
        } else {
            $this->set($offset, $violation);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }
}
