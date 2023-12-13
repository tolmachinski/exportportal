<?php

declare(strict_types=1);

namespace App\Common\Contracts\Bill;

use ExportPortal\Enum\EnumCase;

/**
 * @method static self INIT()
 * @method static self PAID()
 * @method static self CONFIRMED()
 * @method static self UNVALIDATED()
 */
final class BillStatus extends EnumCase
{
    public const INIT = 'init';
    public const PAID = 'paid';
    public const CONFIRMED = 'confirmed';
    public const UNVALIDATED = 'unvalidated';
}
