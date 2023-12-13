<?php

declare(strict_types=1);

namespace App\Common\Contracts\Popup;

use ExportPortal\Enum\EnumCase;

/**
 * @author Anton Zencenco
 *
 * @method static self BY_CONDITION()
 * @method static self ON_LOAD()
 * @method static self COMBINED()
 */
final class PopupCallType extends EnumCase
{
    public const BY_CONDITION = '0';
    public const ON_LOAD = '1';
    public const COMBINED = '2';
}
