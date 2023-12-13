<?php

declare(strict_types=1);

namespace App\Common\Contracts\Media;

use ExportPortal\Enum\EnumCase;

/**
 * @method static self SMALL()
 * @method static self MEDIUM()
 * @method static self BIG()
 */
final class CompanyPhotosThumb extends EnumCase
{
    public const SMALL = 1;
    public const MEDIUM = 2;
    public const BIG = 4;

    public function fileNamePrefix(): string
    {
        return static::getFileNamePrefix($this);
    }

    public static function getFileNamePrefix(self $size): string
    {
        switch ($size) {
            case self::SMALL(): return 'thumb_1_';
            case self::MEDIUM(): return 'thumb_2_';
            case self::BIG(): return 'thumb_4_';
        }
    }

    /**
     * Get width.
     *
     * @return mixed
     */
    public function width()
    {
        return static::getWidth($this);
    }

    public static function getWidth(self $size)
    {
        switch ($size) {
            case self::SMALL(): return 140;
            case self::MEDIUM(): return 220;
            case self::BIG(): return 400;
        }
    }

    /**
     * Get height.
     *
     * @return mixed
     */
    public function height()
    {
        return static::getHeight($this);
    }

    public static function getHeight(self $size)
    {
        switch ($size) {
            case self::SMALL(): return 'R';
            case self::MEDIUM(): return 'R';
            case self::BIG(): return 'R';
        }
    }
}
