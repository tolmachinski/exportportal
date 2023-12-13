<?php

declare(strict_types=1);

namespace App\Common\Database\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateType;

/**
 * The custom date time.
 */
class LegacyDateType extends DateType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return Types::DATE_LEGACY;
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
