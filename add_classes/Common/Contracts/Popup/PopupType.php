<?php

declare(strict_types=1);

namespace App\Common\Contracts\Popup;

use ExportPortal\Enum\EnumCase;

/**
 * @author Anton Zencenco
 *
 * @method static self POPUP()
 * @method static self PAGE()
 */
final class PopupType extends EnumCase
{
    public const PAGE = 'page';
    public const POPUP = 'popup';
}
