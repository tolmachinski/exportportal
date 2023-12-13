<?php

declare(strict_types=1);

namespace App\Common\Contracts\Calendar;

use ExportPortal\Enum\EnumCase;

/**
 * @method static self EMAIL()
 * @method static self SYSTEM()
 */
final class NotificationType extends EnumCase
{
    public const EMAIL = 'email';
    public const SYSTEM = 'system';
}
