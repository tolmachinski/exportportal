<?php

declare(strict_types=1);

namespace App\Common\Database\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Exception;
use Money\Currency;
use Money\Money;

/**
 * The type that maps an MySQL ENUM to a PHP string.
 */
class MoneyType extends Type
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return Types::MONEY;
    }

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return $platform->getJsonTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * {@inheritdoc}
     *
     * @param null|Money $value
     *
     * @return mixed
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }
        if (!$value instanceof Money) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), Money::class);
        }

        $encoded = json_encode($value);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw ConversionException::conversionFailedSerialization($value, 'json', json_last_error_msg());
        }

        return $encoded;
    }

    /**
     * {@inheritdoc}
     *
     * @return null|Money
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (\is_resource($value)) {
            $value = \stream_get_contents($value);
        }

        $decoded = \json_decode($value, true);
        if (JSON_ERROR_NONE !== \json_last_error()) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }

        if (!isset($decoded['amount']) || !isset($decoded['currency'])) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }

        try {
            $money = new Money($decoded['amount'], new Currency($decoded['currency']));
        } catch (Exception $exception) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }

        return $money;
    }
}
