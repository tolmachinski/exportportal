<?php

declare(strict_types=1);

namespace App\Common\Database\Concerns;

use App\Common\Database\Platforms\MySQL\Types\Types as MySQLTypes;
use App\Common\Database\Types\Types as CustomTypes;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * Normalizes the types for casts.
 */
trait NormalizesTypes
{
    private array $typesMapping = [
        'int'      => Types::INTEGER,
        'integer'  => Types::INTEGER,
        'float'    => Types::FLOAT,
        'double'   => Types::FLOAT,
        'decimal'  => Types::DECIMAL,
        'bool'     => Types::BOOLEAN,
        'boolean'  => Types::BOOLEAN,
        'set'      => MySQLTypes::SET,
        'enum'     => MySQLTypes::ENUM,
        'string'   => Types::STRING,
        'json'     => CustomTypes::JSON_LEGACY,
        'array'    => CustomTypes::JSON_LEGACY,
        'object'   => CustomTypes::JSON_LEGACY,
        'money'    => CustomTypes::SIMPLE_MONEY,
        'date'     => CustomTypes::DATE_LEGACY,
        'time'     => CustomTypes::TIME_LEGACY,
        'datetime' => CustomTypes::DATETIME_LEGACY,
    ];

    /**
     * Normalizes the casts types.
     */
    private function normalizeAttribuesCasts(array $casts): array
    {
        $processed = [];
        foreach ($casts as $column => $cast) {
            $realType = $this->typesMapping[$cast] ?? $cast;
            if (!Type::hasType($realType)) {
                $realType = Types::STRING;
            }

            $processed[$column] = $realType;
        }

        return $processed;
    }
}
