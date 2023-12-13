<?php

declare(strict_types=1);

namespace App\Common\Contracts\Cancel;

use ExportPortal\Enum\EnumCase;

/**
 * @author Anton Zencenco
 *
 * @method static self INIT()
 * @method static self DELETED()
 * @method static self CONFIRMED()
 * @method static self CANCELED()
 */
final class CancellationRequestStatus extends EnumCase
{
    public const INIT = 'init';
    public const DELETED = 'deleted';
    public const CONFIRMED = 'confirmed';
    public const CANCELED = 'canceled';
}
