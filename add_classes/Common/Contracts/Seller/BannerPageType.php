<?php

declare(strict_types=1);

namespace App\Common\Contracts\Seller;

use ExportPortal\Enum\EnumCase;

/**
 * @method static self HOME()
 * @method static self STORE()
 * @method static self BOTH()
 */
final class BannerPageType extends EnumCase
{
    public const HOME = 'home';
    public const STORE = 'store';
    public const BOTH = 'both';
}
