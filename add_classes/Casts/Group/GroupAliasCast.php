<?php

declare(strict_types=1);

namespace App\Casts\Group;

use App\Common\Contracts\Group\GroupAlias;
use App\Common\Database\AttributeCastInterface;
use App\Common\Database\Model;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class GroupAliasCast implements AttributeCastInterface
{
    /**
     * {@inheritdoc}
     */
    public function get(AbstractPlatform $platform, Model $model, string $key, $value, array $attributes = [])
    {
        if (null === $value) {
            return null;
        }

        return GroupAlias::from($value);
    }

    /**
     * {@inheritdoc}
     */
    public function set(AbstractPlatform $platform, Model $model, string $key, $value, array $attributes = [])
    {
        if (!$value instanceof GroupAlias) {
            return null;
        }

        return $value->value;
    }
}
