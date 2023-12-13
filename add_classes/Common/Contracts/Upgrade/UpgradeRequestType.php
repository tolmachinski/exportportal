<?php

declare(strict_types=1);

namespace App\Common\Contracts\Upgrade;

use ExportPortal\Enum\EnumCase;

/**
 * @author Anton Zencenco
 *
 * @method static self EXTEND()
 * @method static self UPGRADE()
 * @method static self DOWNGRADE()
 */
final class UpgradeRequestType extends EnumCase
{
    public const EXTEND = 'extend';
    public const UPGRADE = 'upgrade';
    public const DOWNGRADE = 'downgrade';
}
