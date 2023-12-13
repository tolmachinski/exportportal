<?php

declare(strict_types=1);

namespace App\Common\Contracts\Notification;

use ExportPortal\Enum\EnumCase;

/**
 * @author Anton Zencenco
 *
 * @method static self NOTICE()
 * @method static self WARNING()
 */
final class NotificationCategory extends EnumCase
{
    public const NOTICE = 'notice';
    public const WARNING = 'warning';
}
