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
class UuidBinaryType extends Type
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return Types::UUID_BINARY;
    }

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return $platform->getBinaryTypeDeclarationSQL(
            [
                'length' => '16',
                'fixed'  => true,
            ]
        );
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
            return Uuid::fromBytes($value);
        } catch (InvalidArgumentException $e) {
            throw ConversionException::conversionFailed($value, Types::UUID_BINARY, $e);
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

        if ($value instanceof UuidInterface) {
            return $value->getBytes();
        }

        try {
            if (
                (is_string($value) || method_exists($value, '__toString')) && Uuid::isValid($uuid = (string) $value)
            ) {
                return Uuid::fromString($uuid)->getBytes();
            }
        } catch (InvalidArgumentException $e) {
            // Not catch for you
        }

        throw ConversionException::conversionFailed($value, Types::UUID_BINARY, $e ?? null);
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
