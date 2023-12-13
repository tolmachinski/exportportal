<?php

declare(strict_types=1);

namespace App\Common\Contracts\Document;

use ExportPortal\Enum\EnumCase;

/**
 * @author Anton Zencenco
 *
 * @method static self PERSONAL()
 * @method static self BUSINESS()
 * @method static self OTHER()
 */
final class DocumentTypeCategory extends EnumCase
{
    public const PERSONAL = 'personal';
    public const BUSINESS = 'business';
    public const OTHER = 'other';

    /**
     * Gets the label for enum case.
     */
    public static function getLabel(self $value): string
    {
        switch ($value) {
            case DocumentTypeCategory::PERSONAL(): return 'Personal';
            case DocumentTypeCategory::BUSINESS(): return 'Business';
            case DocumentTypeCategory::OTHER(): return 'Other';
        }
    }
}
