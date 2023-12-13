<?php

declare(strict_types=1);

namespace App\Bridge\Matrix;

use ExportPortal\Enum\EnumCase;

/**
 * @method static self PUBLIC_VISIBILITY()
 * @method static self PRIVATE_VISIBILITY()
 */
final class RoomVisibility extends EnumCase
{
    public const PUBLIC_VISIBILITY = 'public';
    public const PRIVATE_VISIBILITY = 'private';
}
