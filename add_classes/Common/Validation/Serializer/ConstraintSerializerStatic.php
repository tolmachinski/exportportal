<?php

namespace App\Common\Validation\Serializer;

use App\Common\Serializer\SerializerAdapterInterface;
use App\Common\Serializer\StaticSerializerAdapterInterface;

final class ConstraintSerializerStatic implements StaticSerializerAdapterInterface
{
    /**
     * The internal serializer.
     *
     * @var SerializerAdapterInterface
     */
    private static $serializer;

    /**
     * Serializes data in the appropriate format.
     *
     * @param mixed  $data    Any data
     * @param string $format  Format name
     * @param array  $context Options normalizers/encoders have access to
     *
     * @return null|string
     */
    public static function serialize($data, $format, array $context = array())
    {
        if (null === $data) {
            return null;
        }

        try {
            return static::getInternalSerializer()->serialize($data, $format, $context);
        } catch (\Exception $exception) {
            return null;
        }
    }

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
    public static function deserialize($data, $type, $format, array $context = array())
    {
        if (null === $data) {
            return null;
        }

        try {
            return static::getInternalSerializer()->deserialize($data, $type, $format, $context);
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * Returns the serializer.
     *
     * @return SerializerAdapterInterface
     */
    private static function getInternalSerializer()
    {
        if (empty(static::$serializer)) {
            static::$serializer = static::resolveInternalSerialzier();
        }

        return static::$serializer;
    }

    /**
     * Creates internal serializer.
     *
     * @return SerializerAdapterInterface
     */
    private static function resolveInternalSerialzier()
    {
        return new ConstraintSerializerAdapter();
    }
}
