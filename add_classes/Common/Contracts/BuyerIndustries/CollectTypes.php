<?php

declare(strict_types=1);

namespace App\Common\Contracts\BuyerIndustries;

use ExportPortal\Enum\EnumCase;

/**
 * @author Bendiudov Tatiana
 *
 * @method static self ITEM()
 * @method static self SEARCH_ITEM()
 * @method static self CATEGORY_PAGE()
 * @method static self SELLER_PAGE()
 * @method static self SEARCH_CATEGORY()
 */
final class CollectTypes extends EnumCase
{
    public const ITEM = 'item';
    public const SEARCH_ITEM = 'search_item';
    public const CATEGORY_PAGE = 'category_page';
    public const SELLER_PAGE = 'seller_page';
    public const SEARCH_CATEGORY = 'search_category';
}
