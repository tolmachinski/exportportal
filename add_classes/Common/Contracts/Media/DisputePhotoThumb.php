<?php

declare(strict_types=1);

namespace App\Common\Contracts\Media;

use ExportPortal\Enum\EnumCase;

/**
 * @method static self SMALL()
 */
final class DisputePhotoThumb extends EnumCase
{
    public const SMALL = 0;

    public function fileNamePrefix(): string
    {
        return static::getFileNamePrefix($this);
    }

    public static function getFileNamePrefix(self $size): string
    {
        switch ($size) {
            case self::SMALL(): return 'thumb_Rx60_';
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
            case self::SMALL(): return 'R';
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
            case self::SMALL(): return 60;
        }
    }
}
