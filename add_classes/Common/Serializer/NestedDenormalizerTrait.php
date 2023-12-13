<?php

namespace App\Common\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

trait NestedDenormalizerTrait
{
    /**
     * Denormalizes the nested object.
     *
     * @param SerializerAdapterInterface $adapter
     * @param mixed                      $innerObject
     * @param string                     $type
     * @param string                     $format
     *
     * @return mixed
     */
    private function denormalizeNestedObject(SerializerAdapterInterface $adapter, $innerObject, $type, $format)
    {
        if (null === $innerObject) {
            return null;
        }

        $serializer = $adapter->getSerializer();
        $contextInstance = $adapter->getContext();
        if (is_array($innerObject) && $serializer instanceof DenormalizerInterface) {
            return $serializer->denormalize($innerObject, $type, $format, $contextInstance->getContext());
        }

        return $serializer->deserialize($innerObject, $type, $format, $contextInstance->getContext());
    }
}
