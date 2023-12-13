<?php

declare(strict_types=1);

namespace App\Common\Contracts\Upgrade;

use ExportPortal\Enum\EnumCase;

/**
 * @author Anton Zencenco
 *
 * @method static self FRESH()
 * @method static self CANCELED()
 * @method static self CONFIRMED()
 */
final class UpgradeRequestStatus extends EnumCase
{
    public const FRESH = 'new';
    public const CANCELED = 'canceled';
    public const CONFIRMED = 'confirmed';
}
