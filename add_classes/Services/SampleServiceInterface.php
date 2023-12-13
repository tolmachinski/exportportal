<?php

declare(strict_types=1);

namespace App\Services;

interface SampleServiceInterface
{
    public const ITEM_NOT_FOUND_ERROR = 0x000035001;
    public const USER_NOT_FOUND_ERROR = 0x000035002;
    public const PAGE_NOT_FOUND_ERROR = 0x000035003;
    public const ITEMS_NOT_FOUND_ERROR = 0x000035004;
    public const ORDER_NOT_FOUND_ERROR = 0x000035005;
    public const THEME_NOT_FOUND_ERROR = 0x000035006;
    public const STATUS_NOT_FOUND_ERROR = 0x000035007;
    public const SNAPSHOT_NOT_FOUND_ERROR = 0x000035008;
    public const BILLS_NOT_FOUND_ERROR = 0x000035009;
    public const MESSAGE_NOT_FOUND_ERROR = 0x000035010;

    public const ITEM_OWNERSHIP_ERROR = 0x000035101;
    public const ITEMS_OWNERSHIP_ERROR = 0x000035102;
    public const CONFIRM_PAYMENT_ORDER_ERROR = 0x000035103;
    public const ORDER_OWNERSHIP_ERROR = 0x000035104;
    public const THEME_OWNERSHIP_ERROR = 0x000035105;

    public const ITEM_ACCESS_DENIED_ERROR = 0x000035201;
    public const ITEMS_ACCESS_DENIED_ERROR = 0x000035202;
    public const ORDER_ACCESS_DENIED_ERROR = 0x000035203;
    public const USER_ACCESS_DENIED_ERROR = 0x000035204;

    public const ALREADY_ASSIGNED_ORDER_ERROR = 0x000035301;

    public const STORAGE_WRITE_ERROR = 0x000035501;
    public const STORAGE_UPDATE_ERROR = 0x000035502;
}
