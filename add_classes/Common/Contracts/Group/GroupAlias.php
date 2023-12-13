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
 * @method static self SHIPPER()
 * @method static self SUPPORT()
 * @method static self SUPER_ADMIN()
 * @method static self USER_MANAGER()
 * @method static self COUNTRY_LEAD()
 * @method static self CUSTOM_GROUP()
 * @method static self ORDER_MANAGER()
 * @method static self CONTENT_MANAGER()
 * @method static self BILLING_MANAGER()
 * @method static self BRAND_AMBASADOR()
 * @method static self VERIFIED_SELLER()
 * @method static self CERTIFIED_SELLER()
 * @method static self COMPANY_STAFF_USER()
 * @method static self SHIPPER_STAFF_USER()
 * @method static self CONTENT_TRANSLATOR()
 * @method static self VERIFIED_MANUFACTURER()
 * @method static self CERTIFIED_MANUFACTURER()
 * @method static self INTERNATIONAL_MEDIATOR()
 */
final class GroupAlias extends EnumCase
{
    public const ADMIN = 'admin';
    public const BUYER = 'buyer';
    public const SHIPPER = 'shipper';
    public const SUPPORT = 'support';
    public const SUPER_ADMIN = 'super-admin';
    public const USER_MANAGER = 'user-manager';
    public const COUNTRY_LEAD = 'country-lead';
    public const CUSTOM_GROUP = 'custom-group';
    public const ORDER_MANAGER = 'order-manager';
    public const CONTENT_MANAGER = 'content-manager';
    public const BILLING_MANAGER = 'billing-manager';
    public const BRAND_AMBASADOR = 'brand-ambasador';
    public const VERIFIED_SELLER = 'verified-seller';
    public const CERTIFIED_SELLER = 'certified-seller';
    public const COMPANY_STAFF_USER = 'company-staff-user';
    public const SHIPPER_STAFF_USER = 'shipper-staff-user';
    public const CONTENT_TRANSLATOR = 'content-translator';
    public const VERIFIED_MANUFACTURER = 'verified-manufacturer';
    public const CERTIFIED_MANUFACTURER = 'certified-manufacturer';
    public const INTERNATIONAL_MEDIATOR = 'international-mediator';

    /**
     * Determine if group alias belongs to the certitfied groups.
     */
    public function isCertified(): bool
    {
        return \in_array($this->value, [
            static::CERTIFIED_MANUFACTURER,
            static::CERTIFIED_SELLER,
        ]);
    }

    /**
     * Returns the group type for alias.
     */
    public function type(): GroupType
    {
        switch ($this->value) {
            case static::ADMIN: return GroupType::from(GroupType::ADMIN);
            case static::BUYER: return GroupType::from(GroupType::BUYER);
            case static::SHIPPER: return GroupType::from(GroupType::SHIPPER);
            case static::SUPPORT: return GroupType::from(GroupType::EP_STAFF);
            case static::SUPER_ADMIN: return GroupType::from(GroupType::ADMIN);
            case static::USER_MANAGER: return GroupType::from(GroupType::EP_STAFF);
            case static::COUNTRY_LEAD: return GroupType::from(GroupType::CR_AFFILIATE);
            case static::CUSTOM_GROUP: return GroupType::from(GroupType::EP_STAFF);
            case static::ORDER_MANAGER: return GroupType::from(GroupType::EP_STAFF);
            case static::CONTENT_MANAGER: return GroupType::from(GroupType::EP_STAFF);
            case static::BILLING_MANAGER: return GroupType::from(GroupType::EP_STAFF);
            case static::BRAND_AMBASADOR: return GroupType::from(GroupType::CR_AFFILIATE);
            case static::VERIFIED_SELLER: return GroupType::from(GroupType::SELLER);
            case static::CERTIFIED_SELLER: return GroupType::from(GroupType::SELLER);
            case static::COMPANY_STAFF_USER: return GroupType::from(GroupType::COMPANY_STAFF);
            case static::SHIPPER_STAFF_USER: return GroupType::from(GroupType::SHIPPER_STAFF);
            case static::CONTENT_TRANSLATOR: return GroupType::from(GroupType::EP_STAFF);
            case static::VERIFIED_MANUFACTURER: return GroupType::from(GroupType::SELLER);
            case static::CERTIFIED_MANUFACTURER: return GroupType::from(GroupType::SELLER);
            case static::INTERNATIONAL_MEDIATOR: return GroupType::from(GroupType::CR_AFFILIATE);
        }

        throw new UnhandledMatchError('The provided type is not supported.');
    }

    /**
     * List of ids
     */
    public static function getGroupIdByAlias(self $alias): int
    {
        switch ($alias) {
            case static::ADMIN: return 14;
            case static::BUYER: return 1;
            case static::SHIPPER: return 31;
            case static::SUPPORT: return 17;
            case static::SUPER_ADMIN: return 16;
            case static::USER_MANAGER: return 15;
            case static::COUNTRY_LEAD: return 34;
            case static::CUSTOM_GROUP: return 36;
            case static::ORDER_MANAGER: return 13;
            case static::CONTENT_MANAGER: return 18;
            case static::BILLING_MANAGER: return 19;
            case static::BRAND_AMBASADOR: return 35;
            case static::VERIFIED_SELLER: return 2;
            case static::CERTIFIED_SELLER: return 3;
            case static::COMPANY_STAFF_USER: return 25;
            case static::SHIPPER_STAFF_USER: return 32;
            case static::CONTENT_TRANSLATOR: return 37;
            case static::VERIFIED_MANUFACTURER: return 5;
            case static::CERTIFIED_MANUFACTURER: return 6;
            case static::INTERNATIONAL_MEDIATOR: return 33;
        }

        throw new UnhandledMatchError('The provided type is not supported.');
    }

    /**
     * Get ids of groups alias
     */
    public static function getGroupsIdsByAliases(...$aliases): ?array
    {
        $list = [];

        foreach ($aliases as $alias) {
            $list[] = self::getGroupIdByAlias($alias);
        }

        return $list;
    }
}
