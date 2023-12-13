<?php

declare(strict_types=1);

namespace App\Bridge\Matrix;

use ExportPortal\Enum\EnumCase;

/**
 * @method static self PUBLIC_CHAT()
 * @method static self PRIVATE_CHAT()
 * @method static self TRUSTED_PRIVATE_CHAT()
 */
final class RoomPreset extends EnumCase
{
    public const PUBLIC_CHAT = 'public_chat';
    public const PRIVATE_CHAT = 'private_chat';
    public const TRUSTED_PRIVATE_CHAT = 'trusted_private_chat';
}
