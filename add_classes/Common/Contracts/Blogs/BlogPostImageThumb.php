<?php

declare(strict_types=1);

namespace App\Common\Contracts\Blogs;

use ExportPortal\Enum\EnumCase;

/**
 * @method static self BIG()
 * @method static self SMALL()
 * @method static self MEDIUM()
 */
final class BlogPostImageThumb extends EnumCase
{
    public const BIG = 2;
    public const SMALL = 0;
    public const MEDIUM = 1;

    /**
     * Get file prefix.
     */
    public function filePrefix(): string
    {
        return static::getFilePrefix($this);
    }

    /**
     * Get file prefix for enum case.
     */
    public static function getFilePrefix(self $size): string
    {
        switch ($size) {
            case self::BIG(): return 'thumb_660xR';
            case self::SMALL(): return 'thumb_200xR';
            case self::MEDIUM(): return 'thumb_364xR';
        }
    }

    /**
     * Get thumb width.
     *
     * @return int|string
     */
    public function width()
    {
        return static::getWidth($this);
    }

    /**
     * Get thumb width for enum case.
     *
     * @return int|string
     */
    public static function getWidth(self $size)
    {
        switch ($size) {
            case self::BIG(): return 660;
            case self::SMALL(): return 200;
            case self::MEDIUM(): return 364;
        }
    }

    /**
     * Get thumb height.
     *
     * @return int|string
     */
    public function height()
    {
        return static::getHeight($this);
    }

    /**
     * Get thumb height for enum case.
     *
     * @return int|string
     */
    public static function getHeight(self $size)
    {
        switch ($size) {
            case self::BIG(): return 'R';
            case self::SMALL(): return 'R';
            case self::MEDIUM(): return 'R';
        }
    }
}
