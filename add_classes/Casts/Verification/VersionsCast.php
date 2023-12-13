<?php

declare(strict_types=1);

namespace App\Casts\Verification;

use App\Common\Database\AttributeCastInterface;
use App\Common\Database\Model;
use App\Documents\Serializer\VersionSerializerStatic;
use App\Documents\Versioning\VersionList;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class VersionsCast implements AttributeCastInterface
{
    /**
     * {@inheritdoc}
     */
    public function get(AbstractPlatform $platform, Model $model, string $key, $value, array $attributes = [])
    {
        try {
            return VersionSerializerStatic::deserialize($value, VersionList::class, 'json');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set(AbstractPlatform $platform, Model $model, string $key, $value, array $attributes = [])
    {
        try {
            return VersionSerializerStatic::serialize($value, 'json');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
