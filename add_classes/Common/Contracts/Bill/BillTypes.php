<?php

declare(strict_types=1);

namespace App\Common\Contracts\Bill;

use ExportPortal\Enum\EnumCase;
use UnhandledMatchError;

/**
 * @author Anton Zencenco
 *
 * @method static self ORDER()
 * @method static self SHIPPING()
 * @method static self FEATURE_ITEM()
 * @method static self HIGHLIGHT_ITEM()
 * @method static self GROUP_PACKAGE()
 * @method static self USER_RIGHT()
 * @method static self SAMPLE_ORDER()
 */
final class BillTypes extends EnumCase
{
    public const ORDER = 'order';
    public const SHIPPING = 'ship';
    public const FEATURE_ITEM = 'feature_item';
    public const HIGHLIGHT_ITEM = 'highlight_item';
    public const GROUP_PACKAGE = 'group';
    public const USER_RIGHT = 'right';
    public const SAMPLE_ORDER = 'sample_order';

    /**
     * Gets the ID for enum case.
     */
    public static function getId(self $value): int
    {
        switch ($value) {
            case BillTypes::ORDER(): return 1;
            case BillTypes::SHIPPING(): return 2;
            case BillTypes::USER_RIGHT(): return 6;
            case BillTypes::FEATURE_ITEM(): return 3;
            case BillTypes::SAMPLE_ORDER(): return 7;
            case BillTypes::GROUP_PACKAGE(): return 5;
            case BillTypes::HIGHLIGHT_ITEM(): return 4;
        }

        throw new UnhandledMatchError('The provided type is not supported.');
    }
}
