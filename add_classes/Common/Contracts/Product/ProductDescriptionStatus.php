<?php

declare(strict_types=1);

namespace App\Common\Contracts\Product;

use ExportPortal\Enum\EnumCase;

/**
 * @method static self INIT()
 * @method static self NEED_TRANSLATE()
 * @method static self TRANSLATED()
 * @method static self REMOVED()
 */
final class ProductDescriptionStatus extends EnumCase
{
    public const INIT = 'init';
    public const NEED_TRANSLATE = 'need_translate';
    public const TRANSLATED = 'translated';
    public const REMOVED = 'removed';
}
