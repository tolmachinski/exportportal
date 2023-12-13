<?php

declare(strict_types=1);

namespace App\Casts\Order;

use App\Common\Contracts\Order\ProductOrderType;
use App\Common\Database\AttributeCastInterface;
use App\Common\Database\Model;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class ProductOrderTypeCast implements AttributeCastInterface
{
    /**
     * {@inheritdoc}
     */
    public function get(AbstractPlatform $platform, Model $model, string $key, $value, array $attributes = [])
    {
        if (null === $value) {
            return null;
        }

        return ProductOrderType::tryFrom($value);
    }

    /**
     * {@inheritdoc}
     */
    public function set(AbstractPlatform $platform, Model $model, string $key, $value, array $attributes = [])
    {
        if (!$value instanceof ProductOrderType) {
            return null;
        }

        return $value->value;
    }
}
