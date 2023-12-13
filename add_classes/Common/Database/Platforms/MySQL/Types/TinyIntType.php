<?php

declare(strict_types=1);

namespace App\Common\Database\Platforms\MySQL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\IntegerType;

/**
 * The type that maps an MySQL TINYINT to a PHP string.
 */
class TinyIntType extends IntegerType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return Types::TINYINT;
    }

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return 'TINY' . $platform->getIntegerTypeDeclarationSQL($fieldDeclaration);
    }
}
