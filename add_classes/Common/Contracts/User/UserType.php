<?php

declare(strict_types=1);

namespace App\Common\Contracts\User;

use ExportPortal\Enum\EnumCase;

/**
 * @author Anton Zencenco
 *
 * @method static self USER()
 * @method static self SHIPPER()
 * @method static self EP_STAFF()
 * @method static self USERS_STAFF()
 * @method static self SHIPPER_STAFF()
 */
final class UserType extends EnumCase
{
    const USER = 'user';
    const SHIPPER = 'shipper';
    const EP_STAFF = 'ep_staff';
    const USERS_STAFF = 'users_staff';
    const SHIPPER_STAFF = 'shipper_staff';
}
