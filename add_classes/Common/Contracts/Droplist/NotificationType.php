<?php

declare(strict_types=1);

namespace App\Common\Contracts\Droplist;

use UnhandledMatchError;
use ExportPortal\Enum\EnumCase;

/**
 * Get droplist notification type
 * 
 * @method static self WEBSITE()
 * @method static self EMAIL()
 * @method static self BOTH()
 */
final class NotificationType extends EnumCase
{
    public const WEBSITE = 'Website';
    public const EMAIL   = 'Email';
    public const BOTH    = 'Both';

    /**
     * Return type by value
     */
    public static function fromFormValue(int $value): self
    {
        switch ($value) {
            case 1: return self::WEBSITE();
            case 2: return self::EMAIL();
            case 3: return self::BOTH();
        }

        throw new UnhandledMatchError('The provided type is not supported.');
    }

    /**
     * Return value by type
     */
    public static function getFormValue(self $value): int
    {
        switch ($value) {
            case self::WEBSITE(): return 1;
            case self::EMAIL(): return 2;
            case self::BOTH(): return 3;
        }

        throw new UnhandledMatchError('The provided type is not supported.');
    }
}
