<?php

namespace App\Common\Serializer\Mapping;

use Symfony\Component\Serializer\Mapping\ClassDiscriminatorMapping;

interface ClassDiscriminatorMappingAwareInterface
{
    /**
     * Adds mapping for abstract class or interface.
     *
     * @param string                    $className
     * @param ClassDiscriminatorMapping $mapping
     */
    public function addClassMapping($className, ClassDiscriminatorMapping $mapping);
}
