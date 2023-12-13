<?php

declare(strict_types=1);

namespace App\Common\Contracts\EditRequest;

use ExportPortal\Enum\EnumCase;

/**
 * @author Anton Zencenco
 *
 * @method static self PENDING()
 * @method static self ACCEPTED()
 * @method static self DECLINED()
 */
final class EditRequestStatus extends EnumCase
{
    public const PENDING = 'pending';
    public const ACCEPTED = 'accepted';
    public const DECLINED = 'declined';

    /**
     * Get the label for current enum case.
     */
    public function label(): string
    {
        return static::getLabel($this);
    }

    /**
     * Get the label for enum case.
     */
    public static function getLabel(self $value): string
    {
        switch ($value) {
            case self::PENDING(): return 'Pending';
            case self::ACCEPTED(): return 'Accepted';
            case self::DECLINED(): return 'Declined';
        }
    }
}
