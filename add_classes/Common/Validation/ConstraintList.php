<?php

namespace App\Common\Validation;

class ConstraintList implements ConstraintListInterface
{
    /**
     * @var ConstraintInterface[]
     */
    private $constraints = [];

    /**
     * Creates a new constraint list.
     *
     * @param ConstraintInterface[] $constraints
     */
    public function __construct(array $constraints = [])
    {
        foreach ($constraints as $constraint) {
            $this->add($constraint);
        }
    }

    /**
     * Converts the constraints into a string for debugging purposes.
     *
     * @return string
     */
    public function __toString()
    {
        $string = '';

        foreach ($this->constraints as $constraint) {
            $string .= $constraint . "\n";
        }

        return $string;
    }

    /**
     * {@inheritdoc}
     */
    public function add(ConstraintInterface $constraint)
    {
        $this->constraints[] = $constraint;
    }

    /**
     * {@inheritdoc}
     */
    public function get($offset)
    {
        if (!isset($this->constraints[$offset])) {
            throw new \OutOfBoundsException(sprintf('The offset "%s" does not exist.', $offset));
        }

        return $this->constraints[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function has($offset)
    {
        return isset($this->constraints[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function merge(ConstraintListInterface $constraintsList)
    {
        foreach ($constraintsList as $key => $constraint) {
            if (isset($this->constraints[$key])) {
                $this->constraints[] = $constraint;
            } else {
                $this->constraints[$key] = $constraint;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set($offset, ConstraintInterface $constraint)
    {
        $this->constraints[$offset] = $constraint;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($offset)
    {
        unset($this->constraints[$offset]);
    }

    /**
     * Clears the violations.
     */
    public function clear(): void
    {
        $this->constraints = [];
    }

    /**
     * {@inheritdoc}
     *
     * @return \ArrayIterator|ConstraintInterface[]
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->constraints);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return \count($this->constraints);
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
    public function offsetSet($offset, $constraint)
    {
        if (null === $offset) {
            $this->add($constraint);
        } else {
            $this->set($offset, $constraint);
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
