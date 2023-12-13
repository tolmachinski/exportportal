<?php

declare(strict_types=1);

namespace App\Common\Contracts\ProductReview;

use ExportPortal\Enum\EnumCase;

/**
 * @method static self NOT_MODERATED()
 * @method static self MODERATED()
 */
final class ProductReviewStatus extends EnumCase
{
    public const FRESH = 'new';
    public const MODERATED = 'moderated';
}
