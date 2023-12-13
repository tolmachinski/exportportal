<?php

declare(strict_types=1);

namespace App\Casts\Product;

use App\Common\Database\AttributeCastInterface;
use App\Common\Database\Model;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class ProductSizeCast implements AttributeCastInterface
{
    /**
     * {@inheritdoc}
     */
    public function get(AbstractPlatform $platform, Model $model, string $key, $value, array $attributes = [])
    {
        if (null === $value || \is_array($value)) {
            return null;
        }
        list($length, $width, $height) = explode('x', $value);

        return \compact('length', 'width', 'height');
    }

    /**
     * {@inheritdoc}
     */
    public function set(AbstractPlatform $platform, Model $model, string $key, $value, array $attributes = [])
    {
        if (null === $value || \is_string($value)) {
            return $value;
        }
        $length = $value['length'] ?? 0;
        $width = $value['width'] ?? 0;
        $height = $value['height'] ?? 0;

        return "{$length}x{$width}x{$height}";
    }
}
