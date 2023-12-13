<?php

declare(strict_types=1);

namespace App\Common\Contracts\Product;

use ExportPortal\Enum\EnumCase;

/**
 * @author Anton Zencenco
 *
 * @method static self RAW()
 * @method static self PARTIAL()
 * @method static self TRANSLATED()
 * @method static self PROCESSING()
 */
final class ProductTranslationState extends EnumCase
{
    public const RAW = 'no';
    public const PARTIAL = 'partial';
    public const TRANSLATED = 'yes';
    public const PROCESSING = 'in_process';
}
