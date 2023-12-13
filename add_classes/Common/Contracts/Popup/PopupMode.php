<?php

declare(strict_types=1);

namespace App\Common\Contracts\Popup;

use ExportPortal\Enum\EnumCase;

/**
 * @author Anton Zencenco
 *
 * @method static self MODAL()
 * @method static self BOTTOM_BANNER()
 */
final class PopupMode extends EnumCase
{
    public const MODAL = 'modal';
    public const BOTTOM_BANNER = 'banner_bottom';
}
