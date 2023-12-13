<?php

declare(strict_types=1);

namespace App\Common\Contracts\Group;

use ExportPortal\Enum\EnumCase;
use UnhandledMatchError;

/**
 * @author Anton Zencenco
 *
 * @method static self ADMIN()
 * @method static self BUYER()
 * @method static self SELLER()
 * @method static self SHIPPER()
 * @method static self EP_STAFF()
 * @method static self CR_AFFILIATE()
 * @method static self COMPANY_STAFF()
 * @method static self SHIPPER_STAFF()
 */
final class GroupType extends EnumCase
{
    public const ADMIN = 'Admin';
    public const BUYER = 'Buyer';
    public const SELLER = 'Seller';
    public const SHIPPER = 'Shipper';
    public const EP_STAFF = 'EP Staff';
    public const CR_AFFILIATE = 'CR Affiliate';
    public const COMPANY_STAFF = 'Company Staff';
    public const SHIPPER_STAFF = 'Shipper Staff';
    public const EP_CLIENTS = 'EP Clients';

    /**
     * Determine if current group if one of the administration groups.
     */
    public function isAdministration(): bool
    {
        return $this->value === static::ADMIN;
    }

    /**
     * Determine if current group if one of the site staff groups.
     */
    public function isStaff(): bool
    {
        return $this->value === static::EP_STAFF;
    }

    /**
     * Returns the list of aliases for group type.
     *
     * @return GroupAlias[]
     */
    public function aliases(): array
    {
        switch ($this->value) {
            case static::ADMIN: return [GroupAlias::from(GroupAlias::ADMIN), GroupAlias::from(GroupAlias::SUPER_ADMIN)];
            case static::BUYER: return [GroupAlias::from(GroupAlias::BUYER)];
            case static::SHIPPER: return [GroupAlias::from(GroupAlias::SHIPPER)];
            case static::SHIPPER_STAFF: return [GroupAlias::from(GroupAlias::SHIPPER_STAFF_USER)];
            case static::COMPANY_STAFF: return [GroupAlias::from(GroupAlias::COMPANY_STAFF_USER)];
            case static::CR_AFFILIATE: return [
                GroupAlias::from(GroupAlias::COUNTRY_LEAD),
                GroupAlias::from(GroupAlias::BRAND_AMBASADOR),
                GroupAlias::from(GroupAlias::INTERNATIONAL_MEDIATOR),
            ];

            case static::SELLER: return [
                GroupAlias::from(GroupAlias::VERIFIED_SELLER),
                GroupAlias::from(GroupAlias::CERTIFIED_SELLER),
                GroupAlias::from(GroupAlias::VERIFIED_MANUFACTURER),
                GroupAlias::from(GroupAlias::CERTIFIED_MANUFACTURER),
            ];

            case static::EP_STAFF: return [
                GroupAlias::from(GroupAlias::SUPPORT),
                GroupAlias::from(GroupAlias::USER_MANAGER),
                GroupAlias::from(GroupAlias::CUSTOM_GROUP),
                GroupAlias::from(GroupAlias::ORDER_MANAGER),
                GroupAlias::from(GroupAlias::CONTENT_MANAGER),
                GroupAlias::from(GroupAlias::BILLING_MANAGER),
                GroupAlias::from(GroupAlias::CONTENT_TRANSLATOR),
            ];

            case static::EP_CLIENTS: return [
                GroupAlias::from(GroupAlias::BUYER),
                GroupAlias::from(GroupAlias::VERIFIED_SELLER),
                GroupAlias::from(GroupAlias::CERTIFIED_SELLER),
                GroupAlias::from(GroupAlias::VERIFIED_MANUFACTURER),
                GroupAlias::from(GroupAlias::CERTIFIED_MANUFACTURER),
                GroupAlias::from(GroupAlias::SHIPPER),
            ];
        }

        throw new UnhandledMatchError('The provided type is not supported.');
    }
}
