<?php

declare(strict_types=1);

namespace App\Common\Contracts;

use ExportPortal\Enum\EnumCase;

/**
 * Get condition operator for DB scope
 * 
 * @method static self GTE()
 * @method static self GT()
 * @method static self LTE()
 * @method static self LT()
 * @method static self EQ()
 */
final class ConditionsOperator extends EnumCase
{
    public const GTE    = 'gte';
    public const GT     = 'gt';
    public const LTE    = 'lte';
    public const LT     = 'lt';
    public const EQ     = 'eq';
}
