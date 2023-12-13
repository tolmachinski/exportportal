<?php

declare(strict_types=1);

namespace App\Common\Database\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;

/**
 * The simple json array type.
 */
class SimpleJsonArrayType extends JsonType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return Types::SIMPLE_JSON_ARRAY;
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $encoded = parent::convertToDatabaseValue($value, $platform)) {
            return null;
        }

        return preg_replace('#^\\[(.*)\\]$#i', '$1', $encoded);
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value || '' === $value) {
            return null;
        }

        return parent::convertToPHPValue($value = null === $value ? null : \sprintf('[%s]', \trim($value, ',')), $platform);
    }
}
