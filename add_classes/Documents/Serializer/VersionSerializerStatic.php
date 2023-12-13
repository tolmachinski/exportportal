<?php

namespace App\Documents\Serializer;

use App\Common\Serializer\SerializerAdapterInterface;
use App\Common\Serializer\StaticSerializerAdapterInterface;
use App\Documents\Versioning\VersionCollectionInterface;
use App\Documents\Versioning\VersionInterface;
use App\Documents\Versioning\VersionList;
use Doctrine\Common\Collections\Collection;

final class VersionSerializerStatic implements StaticSerializerAdapterInterface
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
     *
     * @return null|mixed
     */
    public static function deserialize($data, $type, $format, array $context = array())
    {
        if (null === $data) {
            return null;
        }

        if (
            VersionList::class === $type
            || VersionCollectionInterface::class === $type
            || is_a($type, VersionCollectionInterface::class)
            || is_a($type, Collection::class)
        ) {
            $type = VersionInterface::class . '[]';
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
        return new VersionSerializerAdapter(
            null,
            null,
            null,
            new UserSerializerAdapter(),
            new FileSerializerAdapter(),
            new AttachmentSerializerAdapter()
        );
    }
}
