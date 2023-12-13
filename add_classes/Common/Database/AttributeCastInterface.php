<?php

declare(strict_types=1);

namespace App\Common\Database;

use Doctrine\DBAL\Platforms\AbstractPlatform;

interface AttributeCastInterface
{
    /**
     * Casts the provided value to native value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function get(AbstractPlatform $platform, Model $model, string $key, $value, array $attributes = []);

    /**
     * Casts the provided value to database value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function set(AbstractPlatform $platform, Model $model, string $key, $value, array $attributes = []);
}
