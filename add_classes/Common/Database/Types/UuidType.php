<?php

declare(strict_types=1);

namespace App\Common\Database\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * The type that maps an UUID.
 */
class UuidType extends Type
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return Types::UUID;
    }

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return $platform->getGuidTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * {@inheritdoc}
     *
     * @param null|string|UuidInterface $value
     *
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?UuidInterface
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if ($value instanceof UuidInterface) {
            return $value;
        }

        try {
            return Uuid::fromString($value);
        } catch (InvalidArgumentException $e) {
            throw ConversionException::conversionFailed($value, Types::UUID, $e ?? null);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param null|string|UuidInterface $value
     *
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value || '' === $value) {
            return null;
        }

        try {
            if (
                $value instanceof UuidInterface
                || (
                    (is_string($value) || method_exists($value, '__toString')) && Uuid::isValid((string) $value)
                )
            ) {
                return (string) $value;
            }
        } catch (InvalidArgumentException $e) {
            // Not catch for you
        }

        throw ConversionException::conversionFailed($value, Types::UUID, $e ?? null);
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
