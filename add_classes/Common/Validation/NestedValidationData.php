<?php

namespace App\Common\Validation;

class NestedValidationData implements ValidationDataInterface
{
    /**
     * @var array
     */
    private $elements = array();

    /**
     * Creates a new data set.
     *
     * @param array $violations
     */
    public function __construct(array $data = array())
    {
        foreach ($data as $key => $element) {
            $this->set($key, is_array($element) ? new NestedValidationData($element) : $element);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function add($value)
    {
        $this->elements[] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function get($offset)
    {
        return isset($this->elements[$offset]) ? $this->elements[$offset] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function has($offset)
    {
        return isset($this->elements[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function merge(ValidationDataInterface $dataSet)
    {
        foreach ($dataSet as $key => $element) {
            $this->set($key, $element);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set($offset, $value)
    {
        $this->elements[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($offset)
    {
        unset($this->elements[$offset]);
    }

    /**
     * {@inheritdoc}
     *
     * @return array|\ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return \count($this->elements);
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
