<?php

namespace App\Common\Serializer\Mapping;

use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorMapping;

class ClassDiscriminator implements ClassDiscriminatorMappingAwareInterface, ClassDiscriminatorResolverInterface
{
    /**
     * The list of known mappings.
     *
     * @var ClassDiscriminatorMapping[]
     */
    private $mappings = array();

    public function __construct(array $mappings = array())
    {
        foreach ($mappings as $className => $mapping) {
            $this->addClassMapping($className, $mapping);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addClassMapping($className, ClassDiscriminatorMapping $mapping)
    {
        $this->mappings[$className] = $mapping;
    }

    /**
     * Returns the mapping for class.
     *
     * @param string $class
     *
     * @return null|ClassDiscriminatorMapping
     */
    public function getMappingForClass(string $class): ?ClassDiscriminatorMapping
    {
        if (isset($this->mappings[$class])) {
            return $this->mappings[$class];
        }

        return null;
    }

    /**
     * Returns the mapping for mapped object.
     *
     * @param object|string $object
     *
     * @return null|ClassDiscriminatorMapping
     */
    public function getMappingForMappedObject($object): ?ClassDiscriminatorMapping
    {
        $className = \is_object($object) ? \get_class($object) : $object;
        if (!array_key_exists($className, $this->mappings)) {
            $this->mappings[$className] = $this->resolveMappingForMappedObject($object);
        }

        return $this->mappings[$className];
    }

    /**
     * Returns the type for mapped object.
     *
     * @param object|string $object
     *
     * @return null|string
     */
    public function getTypeForMappedObject($object): ?string
    {
        if (null === $mapping = $this->getMappingForMappedObject($object)) {
            return null;
        }

        return $mapping->getMappedObjectType($object);
    }

    /**
     * Resolves the mapping for the object. Used for inheritnace and interface resolution.
     *
     * @param onject|string $object
     *
     * @return null|ClassDiscriminatorMapping
     */
    private function resolveMappingForMappedObject($object)
    {
        $reflectionClass = new \ReflectionClass($object);
        if ($parentClass = $reflectionClass->getParentClass()) {
            return $this->getMappingForMappedObject($parentClass->getName());
        }
        foreach ($reflectionClass->getInterfaceNames() as $interfaceName) {
            if (null !== ($interfaceMapping = $this->getMappingForMappedObject($interfaceName))) {
                return $interfaceMapping;
            }
        }

        return null;
    }
}
