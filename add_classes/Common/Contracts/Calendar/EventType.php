<?php

declare(strict_types=1);

namespace App\Common\Contracts\Calendar;

use ExportPortal\Enum\EnumCase;

/**
 * @method static self EP_EVENTS()
 */
final class EventType extends EnumCase
{
    public const EP_EVENTS = 'ep_events';
}
