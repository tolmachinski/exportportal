<?php

declare(strict_types=1);

namespace App\Common\Contracts\Media;

use ExportPortal\Enum\EnumCase;

/**
 * @method static self SMALL()
 * @method static self MEDIUM()
 */
final class CompanyLogoThumb extends EnumCase
{
    public const SMALL = 0;
    public const MEDIUM = 1;

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
            case self::SMALL(): return 80;
            case self::MEDIUM(): return 140;
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
        }
    }
}
