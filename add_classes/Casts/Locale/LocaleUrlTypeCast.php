<?php

declare(strict_types=1);

namespace App\Casts\Locale;

use App\Common\Contracts\Locale\LocaleUrlType;
use App\Common\Database\AttributeCastInterface;
use App\Common\Database\Model;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class LocaleUrlTypeCast implements AttributeCastInterface
{
    /**
     * {@inheritdoc}
     */
    public function get(AbstractPlatform $platform, Model $model, string $key, $value, array $attributes = [])
    {
        if (null === $value) {
            return null;
        }

        return LocaleUrlType::from($value);
    }

    /**
     * {@inheritdoc}
     */
    public function set(AbstractPlatform $platform, Model $model, string $key, $value, array $attributes = [])
    {
        if (!$value instanceof LocaleUrlType) {
            return null;
        }

        return $value->value;
    }
}
