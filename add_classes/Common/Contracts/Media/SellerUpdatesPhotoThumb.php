<?php

declare(strict_types=1);

namespace App\Common\Contracts\Media;

use ExportPortal\Enum\EnumCase;

/**
 * @method static self MEDIUM()
 */
final class SellerUpdatesPhotoThumb extends EnumCase
{
    public const MEDIUM = 1;

    public function fileNamePrefix(): string
    {
        return static::getFileNamePrefix($this);
    }

    public static function getFileNamePrefix(self $size): string
    {
        switch ($size) {
            case self::MEDIUM(): return 'thumb_150xR_';
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
            case self::MEDIUM(): return 150;
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
            case self::MEDIUM(): return 'R';
        }
    }
}
