<?php

namespace App\Common\Serializer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

trait NestedNormalizerTrait
{
    /**
     * Normalizes the nested object.
     *
     * @param SerializerAdapterInterface $adapter
     * @param mixed                      $innerObject
     * @param string                     $type
     * @param string                     $format
     *
     * @return mixed
     */
    private function normalizeNestedObject(SerializerAdapterInterface $adapter, $innerObject, $type, $format)
    {
        if (!$innerObject instanceof $type) {
            return null;
        }

        $serializer = $adapter->getSerializer();
        $contextInstance = $adapter->getContext();
        if ('json' === $format && $serializer instanceof NormalizerInterface) {
            return $serializer->normalize($innerObject, $format, $contextInstance->getContext());
        }

        return $serializer->serialize($innerObject, $format, $contextInstance->getContext());
    }
}
