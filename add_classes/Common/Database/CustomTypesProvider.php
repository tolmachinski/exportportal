<?php

declare(strict_types=1);

namespace App\Common\Database;

use App\Common\Database\Platforms\MySQL\Types as MySQLTypes;
use App\Common\Database\Types as CustomTypes;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * Custom DBAL types provider.
 */
final class CustomTypesProvider
{
    /**
     * The list of remapped types.
     * Place here the DB types that must by overriden Doctrine types
     * The format is ['<doctrineType>' => '<dbType>'].
     * By default, each new types will correspond their name.
     */
    private static array $remappedTypes = [];

    /**
     * The base list of the custom types.
     */
    private static array $commonTypes = [
        // Provider types
        // ...

        // Custom types
        CustomTypes\Types::MONEY             => CustomTypes\MoneyType::class,
        CustomTypes\Types::UUID              => CustomTypes\UuidType::class,
        CustomTypes\Types::UUID_BINARY       => CustomTypes\UuidBinaryType::class,
        CustomTypes\Types::SIMPLE_MONEY      => CustomTypes\SimpleMoneyType::class,
        CustomTypes\Types::PHONE_NUMBER      => CustomTypes\PhoneNumberType::class,
        CustomTypes\Types::SIMPLE_JSON_ARRAY => CustomTypes\SimpleJsonArrayType::class,

        // Legacy types
        CustomTypes\Types::JSON_LEGACY     => CustomTypes\LegacyJsonType::class,
        CustomTypes\Types::DATE_LEGACY     => CustomTypes\LegacyDateType::class,
        CustomTypes\Types::TIME_LEGACY     => CustomTypes\LegacyTimeType::class,
        CustomTypes\Types::DATETIME_LEGACY => CustomTypes\LegacyDateTimeType::class,
    ];

    /**
     * The list of the platform-specific types.
     * The format is: ['<platformClassName>' => [...<types>]].
     */
    private static array $platformTypes = [
        // MySQL custom types
        MySqlPlatform::class   => [
            MySQLTypes\Types::BIT     => MySQLTypes\BitType::class,
            MySQLTypes\Types::SET     => MySQLTypes\SetType::class,
            MySQLTypes\Types::ENUM    => MySQLTypes\EnumType::class,
            MySQLTypes\Types::TINYINT => MySQLTypes\TinyIntType::class,
        ],
    ];

    /**
     * Returns the common types mappings.
     */
    public static function getCommonTypes(): array
    {
        return static::$commonTypes;
    }

    /**
     * Returns the common types mappings.
     */
    public static function getPlatformTypes(AbstractPlatform $platform): array
    {
        $types = [];
        foreach (static::$platformTypes as $platformType => $typeList) {
            if (!$platform instanceof $platformType) {
                continue;
            }

            $types = \array_merge($types, $typeList);
        }

        return $types;
    }

    /**
     * Adds custom DBAL types.
     *
     * @deprecated
     */
    public static function bootTypes(AbstractPlatform $platform): void
    {
        // Get the common type
        $types = static::$commonTypes;
        foreach (static::$platformTypes as $platformType => $typeList) {
            if (!$platform instanceof $platformType) {
                continue;
            }

            $types = \array_merge($types, $typeList);
        }

        // Walk the types
        foreach ($types as $doctrineType => $className) {
            if (Type::hasType($doctrineType)) {
                continue;
            }

            // Add types
            Type::addType($doctrineType, $className);
            // Yes, it looks dumb, but this is a proper way to register new types for DB platform.
            // It looks strange, but adding a type into the type registry is not enough.
            $platform->registerDoctrineTypeMapping(static::$remappedTypes[$doctrineType] ?? $doctrineType, $doctrineType);
        }
    }
}
