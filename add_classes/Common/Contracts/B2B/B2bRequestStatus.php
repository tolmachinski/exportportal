<?php

declare(strict_types=1);

namespace App\Common\Contracts\B2B;

use ExportPortal\Enum\EnumCase;

/**
 * @author Anton Zencenco
 *
 * @method static self ENABLED()
 * @method static self DISABLED()
 */
final class B2bRequestStatus extends EnumCase
{
    public const ENABLED = 'enabled';
    public const DISABLED = 'disabled';
}
