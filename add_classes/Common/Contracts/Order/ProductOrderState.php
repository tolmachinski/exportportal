<?php

declare(strict_types=1);

namespace App\Common\Contracts\Order;

use ExportPortal\Enum\EnumCase;

/**
 * @author Anton Zencenco
 *
 * @method static self TRASH()
 * @method static self ACTIVE()
 * @method static self ARCHIVED()
 */
final class ProductOrderState extends EnumCase
{
    public const TRASH = 'trash';
    public const ACTIVE = 'active';
    public const ARCHIVED = 'archived';
}
