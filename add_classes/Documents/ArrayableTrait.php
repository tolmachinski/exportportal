<?php

namespace App\Documents;

use ReflectionClass;
use ReflectionObject;
use ReflectionProperty;

/** @deprecated */
trait ArrayableTrait
{
    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return iterator_to_array($this->getProperties());
    }

    /**
     * Returns the properties of the instance.
     *
     * @return \Generator
     */
    private function getProperties()
    {
        foreach ($this->findAllInstanceProperties(new ReflectionObject($this)) as $property) {
            $property->setAccessible(true);

            yield $property->getName() => $property->getValue($this);
        }
    }

    /**
     * Returns the properties of the provided object reflection.
     *
     * @return array
     */
    private function findAllInstanceProperties(ReflectionClass $object = null)
    {
        if (!$object) {
            return array();
        }
        $parentReflection = $object->getParentClass();

        return array_values(array_merge(
            $parentReflection ? $this->findAllInstanceProperties($parentReflection) : array(),
            array_values(array_filter(
                $object->getProperties(),
                function (ReflectionProperty $property) {
                    return !$property->isStatic();
                }
            ))
        ));
    }
}
