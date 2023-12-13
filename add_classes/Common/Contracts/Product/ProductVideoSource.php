<?php

declare(strict_types=1);

namespace App\Common\Contracts\Product;

use ExportPortal\Enum\EnumCase;

/**
 * @author Anton Zencenco
 *
 * @method static self YOUTUBE()
 * @method static self VIMEO()
 */
final class ProductVideoSource extends EnumCase
{
    public const YOUTUBE = 'youtube';
    public const VIMEO = 'vimeo';
}
