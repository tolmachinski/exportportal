<?php

declare(strict_types=1);

namespace App\Filesystem;

use ExportPortal\Enum\EnumCase;

/**
 * @method static self SMALL()
 * @method static self MEDIUM()
 */
final class EpEventMainImageThumb extends EnumCase
{
    public const SMALL = 0;
    public const MEDIUM = 1;

    /**
     * Get file name prefix.
     */
    public function fileNamePrefix(): string
    {
        return static::getFileNamePrefix($this);
    }

    public static function getFileNamePrefix(self $size): string
    {
        switch ($size) {
            case self::SMALL(): return 'thumb_0_';
            case self::MEDIUM(): return 'thumb_1_';
        }
    }
}
