<?php

declare(strict_types=1);

namespace App\Bridge\Matrix;

use ExportPortal\Enum\EnumCase;

/**
 * @method static self PUBLIC()
 * @method static self KNOCK()
 * @method static self INVITE()
 * @method static self PRIVATE()
 */
final class JoinRule extends EnumCase
{
    public const PUBLIC = 'public';
    public const KNOCK = 'knock';
    public const INVITE = 'invite';
    public const PRIVATE = 'private';
}
