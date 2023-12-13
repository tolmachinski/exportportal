<?php

declare(strict_types=1);

namespace App\Common\Contracts\FeaturedProduct;

use ExportPortal\Enum\EnumCase;

/**
 * @method static self INIT()
 * @method static self ACTIVE()
 * @method static self EXPIRED()
 */
final class FeaturedStatus extends EnumCase
{
    public const INIT = 'init';
    public const ACTIVE = 'active';
    public const EXPIRED = 'expired';
}
