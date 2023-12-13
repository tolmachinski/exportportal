<?php

declare(strict_types=1);

namespace App\Common\Database\Platforms\MySQL\Types;

use Doctrine\DBAL\Types\IntegerType;

/**
 * The type that maps an MySQL SET to a PHP string.
 */
class BitType extends IntegerType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return Types::BIT;
    }
}
