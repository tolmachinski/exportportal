<?php

declare(strict_types=1);

namespace App\Bridge\Matrix;

use ExportPortal\Enum\EnumCase;

/**
 * @method static self MEGOLM_AES_SHA2()
 */
final class EncryptionAlgorithm extends EnumCase
{
    public const MEGOLM_AES_SHA2 = 'm.megolm.v1.aes-sha2';
}
