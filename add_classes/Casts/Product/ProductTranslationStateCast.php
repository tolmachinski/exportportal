<?php

declare(strict_types=1);

namespace App\Casts\Product;

use App\Common\Contracts\Product\ProductTranslationState;
use App\Common\Database\AttributeCastInterface;
use App\Common\Database\Model;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class ProductTranslationStateCast implements AttributeCastInterface
{
    /**
     * {@inheritdoc}
     */
    public function get(AbstractPlatform $platform, Model $model, string $key, $value, array $attributes = [])
    {
        if (null === $value) {
            return null;
        }

        return ProductTranslationState::tryFrom($value);
    }

    /**
     * {@inheritdoc}
     */
    public function set(AbstractPlatform $platform, Model $model, string $key, $value, array $attributes = [])
    {
        if (!$value instanceof ProductTranslationState) {
            return null;
        }

        return $value->value;
    }
}
