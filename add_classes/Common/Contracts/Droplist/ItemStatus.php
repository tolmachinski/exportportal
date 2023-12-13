<?php

declare(strict_types=1);

namespace App\Common\Contracts\Droplist;

use App\Common\Contracts\User\UserStatus;
use ExportPortal\Enum\EnumCase;

/**
 * Get item status in droplist
 * 
 * @method static self ACTIVE()
 * @method static self BLOCKED()
 * @method static self OUT_OF_STOCK()
 * @method static self ON_MODERATION()
 * @method static self DRAFT()
 * @method static self INVISIBLE()
 */
final class ItemStatus extends EnumCase
{
    public const ACTIVE         = 'Active';
    public const BLOCKED        = 'Blocked';
    public const OUT_OF_STOCK   = 'Out of stock';
    public const ON_MODERATION  = 'On moderation';
    public const DRAFT          = 'Draft';
    public const INVISIBLE      = 'Invisible';

    /**
     * Return Item status by conditions
     */
    public static function fromItemParameters(bool $visible, bool $moderated, bool $isOutOfStock, UserStatus $userStatus, bool $draft): self
    {
        if (!$visible) {
            return self::INVISIBLE();
        }

        if (UserStatus::ACTIVE() !== $userStatus) {
            return self::BLOCKED();
        }

        if (!$moderated) {
            return self::ON_MODERATION();
        }

        if ($isOutOfStock) {
            return self::OUT_OF_STOCK();
        }

        if ($draft) {
            return self::DRAFT();
        }

        return self::ACTIVE();
    }

}
