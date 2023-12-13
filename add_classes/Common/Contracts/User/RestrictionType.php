<?php

declare(strict_types=1);

namespace App\Common\Contracts\User;

use ExportPortal\Enum\EnumCase;

/**
 * @author Anton Zencenco
 *
 * @method static self BLOCKING()
 * @method static self RESTRICTION()
 */
final class RestrictionType extends EnumCase
{
    public const BLOCKING = 'blocking';
    public const RESTRICTION = 'restriction';
}
