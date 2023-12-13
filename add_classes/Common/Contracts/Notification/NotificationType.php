<?php

declare(strict_types=1);

namespace App\Common\Contracts\Notification;

use ExportPortal\Enum\EnumCase;

/**
 * @author Anton Zencenco
 *
 * @method static self FRESH()
 * @method static self UODATE()
 */
final class NotificationType extends EnumCase
{
    public const FRESH = 'new';
    public const UODATE = 'updates';
}
