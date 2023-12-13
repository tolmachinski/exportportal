<?php

declare(strict_types=1);

namespace App\Common\Database\Platforms\MySQL\Types;

use Doctrine\DBAL\Types\SimpleArrayType;

/**
 * The type that maps an MySQL SET to a PHP string.
 */
class SetType extends SimpleArrayType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return Types::SET;
    }
}
