<?php

declare(strict_types=1);

namespace App\Bridge\Matrix;

use ExportPortal\Enum\EnumCase;

/**
 * @method static self SHARED()
 * @method static self JOINED()
 * @method static self INVITED()
 * @method static self WORLD_READABLE()
 */
final class HistoryVisibility extends EnumCase
{
    public const SHARED = 'shared';
    public const JOINED = 'joined';
    public const INVITED = 'invited';
    public const WORLD_READABLE = 'world_readable';
}
