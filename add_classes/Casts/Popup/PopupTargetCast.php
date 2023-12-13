<?php

declare(strict_types=1);

namespace App\Casts\Popup;

use App\Common\Contracts\Popup\PopupTarget;
use App\Common\Database\AttributeCastInterface;
use App\Common\Database\Model;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class PopupTargetCast implements AttributeCastInterface
{
    /**
     * {@inheritdoc}
     */
    public function get(AbstractPlatform $platform, Model $model, string $key, $value, array $attributes = [])
    {
        if (null === $value) {
            return null;
        }

        return PopupTarget::tryFrom($value);
    }

    /**
     * {@inheritdoc}
     */
    public function set(AbstractPlatform $platform, Model $model, string $key, $value, array $attributes = [])
    {
        if (!$value instanceof PopupTarget) {
            return null;
        }

        return $value->value;
    }
}
