<?php

declare(strict_types=1);

namespace App\Common\Contracts\User;

use ExportPortal\Enum\EnumCase;

/**
 * @author Anton Zencenco
 *
 * @method static self OK()
 * @method static self BAD()
 * @method static self UNKNOWN()
 */
final class EmailStatus extends EnumCase
{
    public const OK = 'Ok';
    public const BAD = 'Bad';
    public const UNKNOWN = 'Unknown';
}
