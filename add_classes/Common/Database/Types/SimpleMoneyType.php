<?php

declare(strict_types=1);

namespace App\Common\Database\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Exception;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Exception\ParserException;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Money\Parser\AggregateMoneyParser;
use Money\Parser\DecimalMoneyParser;
use Money\Parser\IntlLocalizedDecimalParser;
use Money\Parser\IntlMoneyParser;

/**
 * The type that maps an MySQL ENUM to a PHP string.
 */
class SimpleMoneyType extends Type
{
    private const DEFAULT_CURRENCY = 'USD';

    private const DEFAULT_LOCALE = 'en_US';

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return Types::SIMPLE_MONEY;
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
     * @param null|string $value
     *
     * @return mixed
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof Money) {
            try {
                $value = $this->convertPriceToMoney($value);
            } catch (ParserException $e) {
                throw ConversionException::conversionFailedFormat($value, $this->getName(), Money::class, $e);
            }
        }

        try {
            return (new DecimalMoneyFormatter(new ISOCurrencies()))->format($value);
        } catch (Exception $exception) {
            throw ConversionException::conversionFailedSerialization($value, $this->getName(), $exception->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return null|Money
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value || '' === $value || !is_numeric($value)) {
            return null;
        }

        try {
            return $this->convertPriceToMoney($value);
        } catch (Exception $e) {
            throw ConversionException::conversionFailed($value, $this->getName(), $e);
        }
    }

    /**
     * Converts the price to the Money\Money::class.
     *
     * @param mixed $amount
     */
    private function convertPriceToMoney($amount): Money
    {
        if ($amount instanceof Money) {
            return $amount;
        }
        if (!is_string($amount) && !is_numeric($amount)) {
            throw new ParserException(
                \sprintf('Cannot parse the provided value to %s class.', Money::class)
            );
        }
        if (is_numeric($amount)) {
            return $this->convertNumericPriceToMoney($amount);
        }

        $currency = new Currency(static::DEFAULT_CURRENCY);
        $currencies = new ISOCurrencies();
        $formatter = new \NumberFormatter(static::DEFAULT_LOCALE, \NumberFormatter::CURRENCY);
        $parser = new AggregateMoneyParser([
            new IntlMoneyParser($formatter, $currencies),
            new IntlLocalizedDecimalParser($formatter, $currencies),
            new DecimalMoneyParser($currencies),
        ]);

        try {
            return $parser->parse($amount, $currency);
        } catch (ParserException $exception) {
            try {
                return $parser->parse('$' . trim(trim($amount, '$')), $currency);
            } catch (ParserException $exception) {
                return $parser->parse((string) ($amount + 0), $currency);
            }
        }
    }

    /**
     * Converts the numeric price to the Money\Money::class.
     *
     * @param mixed $amount
     */
    private function convertNumericPriceToMoney($amount): Money
    {
        if (!is_numeric($amount)) {
            throw new ParserException(\sprintf('Cannot parse the provided numeric value to %s class.', Money::class));
        }
        $currency = new Currency(static::DEFAULT_CURRENCY);
        $parser = new DecimalMoneyParser(new ISOCurrencies());

        try {
            return $parser->parse((string) $amount, $currency);
        } catch (ParserException $exception) {
            return $parser->parse((string) ($amount + 0), $currency);
        }
    }
}
