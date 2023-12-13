<?php

declare(strict_types=1);

namespace App\Bridge\Matrix;

use ExportPortal\Enum\EnumCase;

/**
 * @method static self CAN_JOIN()
 * @method static self FORBIDDEN()
 */
final class GuestAccess extends EnumCase
{
    public const CAN_JOIN = 'can-join';
    public const FORBIDDEN = 'forbidden';
}
