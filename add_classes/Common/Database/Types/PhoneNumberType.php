<?php

declare(strict_types=1);

namespace App\Common\Database\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Exception;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

/**
 * The Doctrine DBAL phone number type.
 */
class PhoneNumberType extends Type
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return Types::PHONE_NUMBER;
    }

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getVarcharTypeDeclarationSQL(\array_merge($fieldDeclaration, ['length' => 50]));
    }

    /**
     * {@inheritdoc}
     *
     * @param null|string $value
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof PhoneNumber) {
            throw new ConversionException(\sprintf('Expected instance of %s, got %s', PhoneNumber::class, \gettype($value)));
        }

        return PhoneNumberUtil::getInstance()->format($value, PhoneNumberFormat::E164);
    }

    /**
     * {@inheritdoc}
     *
     * @return null|PhoneNumber
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value || $value instanceof PhoneNumber) {
            return $value;
        }

        try {
            return PhoneNumberUtil::getInstance()->parse($value, PhoneNumberUtil::UNKNOWN_REGION);
        } catch (Exception $e) {
            throw ConversionException::conversionFailed($value, $this->getName(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
