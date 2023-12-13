<?php

namespace App\Common\Serializer;

interface StaticSerializerAdapterInterface
{
    /**
     * Serializes data in the appropriate format.
     *
     * @param mixed  $data    Any data
     * @param string $format  Format name
     * @param array  $context Options normalizers/encoders have access to
     *
     * @return null|string
     */
    public static function serialize($data, $format, array $context = array());

    /**
     * Deserializes data into the given type.
     *
     * @param mixed  $data
     * @param string $type
     * @param string $format
     * @param array  $context
     *
     * @return null|mixed
     */
    public static function deserialize($data, $type, $format, array $context = array());
}
