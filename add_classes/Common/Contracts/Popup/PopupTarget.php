<?php

declare(strict_types=1);

namespace App\Common\Contracts\Popup;

use ExportPortal\Enum\EnumCase;

/**
 * @author Anton Zencenco
 *
 * @method static self ALL()
 * @method static self LOGGED()
 * @method static self NOT_LOGGED()
 */
final class PopupTarget extends EnumCase
{
    public const ALL = 'all';
    public const LOGGED = 'logged';
    public const NOT_LOGGED = 'not_logged';
}
