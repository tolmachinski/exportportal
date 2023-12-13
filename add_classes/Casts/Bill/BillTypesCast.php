<?php

declare(strict_types=1);

namespace App\Casts\Bill;

use App\Common\Contracts\Bill\BillTypes;
use App\Common\Database\AttributeCastInterface;
use App\Common\Database\Model;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class BillTypesCast implements AttributeCastInterface
{
    /**
     * {@inheritdoc}
     */
    public function get(AbstractPlatform $platform, Model $model, string $key, $value, array $attributes = [])
    {
        if (null === $value) {
            return null;
        }

        return BillTypes::tryFrom($value);
    }

    /**
     * {@inheritdoc}
     */
    public function set(AbstractPlatform $platform, Model $model, string $key, $value, array $attributes = [])
    {
        if (!$value instanceof BillTypes) {
            return null;
        }

        return $value->value;
    }
}
