<?php

declare(strict_types=1);

namespace App\Common\Database\Platforms\MySQL\Types;

/**
 * Default built-in types for MySQL.
 */
final class Types
{
    public const TINYINT = 'tinyint';
    public const ENUM = 'enum';
    public const SET = 'set';
    public const BIT = 'bit';

    /**
     * @internal Prevents direct instantaniation
     */
    private function __construct()
    {
        // None shall pass!
    }
}
