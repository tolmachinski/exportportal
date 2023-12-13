<?php

declare(strict_types=1);

namespace App\Common\Contracts\Shipper;

use ExportPortal\Enum\EnumCase;

/**
 * @author Anton Zencenco
 *
 * @method static self SHIPPER()
 * @method static self INTERNATIONAL_SHIPPER()
 */
final class ShipperType extends EnumCase
{
    public const SHIPPER = 'ep_shipper';
    public const INTERNATIONAL_SHIPPER = 'ishipper';
}
