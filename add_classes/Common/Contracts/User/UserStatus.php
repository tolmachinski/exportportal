<?php

declare(strict_types=1);

namespace App\Common\Contracts\User;

use ExportPortal\Enum\EnumCase;

/**
 * @author Anton Zencenco
 *
 * @method static self FRESH()
 * @method static self ACTIVE()
 * @method static self PENDING()
 * @method static self BLOCKED()
 * @method static self DELETED()
 * @method static self RESTRICTED()
 * @method static self AWAITING()
 * @method static self INACTIVE()
 * @method static self BANNED()
 * @method static self STAFF()
 */
final class UserStatus extends EnumCase
{
    // Used statuses
    public const FRESH = 'new'; // do not use reserved word `new`
    public const ACTIVE = 'active';
    public const PENDING = 'pending';
    public const BLOCKED = 'blocked';
    public const DELETED = 'deleted';
    public const RESTRICTED = 'restricted';
    // Unused statuses
    public const AWAITING = 'awaiting';
    public const INACTIVE = 'inactive';
    public const BANNED = 'banned';
    public const STAFF = 'staff';

    /**
     * Determines if status case is in use.
     */
    public function inUse(): bool
    {
        return \in_array($this->value, [
            static::FRESH,
            static::ACTIVE,
            static::PENDING,
            static::BLOCKED,
            static::DELETED,
            static::RESTRICTED,
        ]);
    }

    /**
     * Determine if status case belongs to the limited category.
     */
    public function isLimited(): bool
    {
        return \in_array($this->value, [
            static::BLOCKED,
            static::DELETED,
            static::RESTRICTED,
        ]);
    }
}
