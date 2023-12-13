<?php

declare(strict_types=1);

namespace App\Common\Contracts\Product;

use ExportPortal\Enum\EnumCase;

/**
 * @author Anton Zencenco
 *
 * @method static self AD()
 * @method static self BC()
 */
final class ProductEra extends EnumCase
{
    public const AD = 'AD';
    public const BC = 'BC';
}
