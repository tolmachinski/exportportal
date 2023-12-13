<?php

namespace App\Plugins\EPDocs;

use function json_encode;

class Credentials implements \JsonSerializable, \IteratorAggregate
{
    /**
     * Casts the object to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Serializes the object to array.
     */
    public function toArray()
    {
        return iterator_to_array($this->getIterator());
    }

    /**
     * Serializes the object to JSON.
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Serializes the object to a value that can be serialized natively by json_encode().
     *
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->toJson();
    }

    /**
     * Retrieve an external iterator.
     *
     * @throws \Exception on failure
     *
     * @return \Traversable
     */
    public function getIterator()
    {
        throw new \RuntimeException('This method must be extended');
    }
}
