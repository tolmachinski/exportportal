<?php

namespace App\Common\Serializer\Mapping;

use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;

interface ClassDiscriminatorResolverAwareInterface
{
    /**
     * Returns the discriminator map.
     *
     * @return ClassDiscriminatorResolverInterface
     */
    public function getClassDiscriminatorResolver();
}
