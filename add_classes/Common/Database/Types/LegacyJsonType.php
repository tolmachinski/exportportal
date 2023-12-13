<?php

declare(strict_types=1);

namespace App\Common\Database\Types;

use ArrayObject;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;
use IteratorAggregate;
use JsonSerializable;

/**
 * The custom date time.
 */
class LegacyJsonType extends JsonType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return Types::JSON_LEGACY;
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (\is_string($value)) {
            return $value;
        }

        if (is_object($value)) {
            if (!$value instanceof JsonSerializable) {
                if (method_exists($value, 'toArray')) {
                    $value = $value->toArray();
                } elseif ($value instanceof ArrayObject) {
                    $value = $value->getArrayCopy();
                } elseif ($value instanceof IteratorAggregate) {
                    $value = iterator_to_array($value);
                }
            }
        }

        return parent::convertToDatabaseValue($value, $platform);
    }
}
