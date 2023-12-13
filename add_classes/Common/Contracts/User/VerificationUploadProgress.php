<?php

declare(strict_types=1);

namespace App\Common\Contracts\User;

use ExportPortal\Enum\EnumCase;

/**
 * @author Anton Zencenco
 *
 * @method static self FULL()
 * @method static self NONE()
 * @method static self PARTIAL()
 */
final class VerificationUploadProgress extends EnumCase
{
    public const FULL ='full';
    public const NONE ='none';
    public const PARTIAL ='partial';
}
