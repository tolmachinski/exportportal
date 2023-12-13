<?php

declare(strict_types=1);

namespace App\Common\Database\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\TimeType;

/**
 * The custom date time.
 */
class LegacyTimeType extends TimeType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return Types::TIME_LEGACY;
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return parent::convertToDatabaseValue(parent::convertToPHPValue($value, $platform), $platform);
    }
}
