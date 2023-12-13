<?php

declare(strict_types=1);

namespace App\Common\Database;

interface ValuesTransformerInterface
{
    /**
     * Transform attributes to database values.
     */
    public function transformToDatabaseValues(array $attributes, bool $forced = false): array;

    /**
     * Transform attributes to PHP values.
     */
    public function transformToPhpValues(array $attributes): array;
}
