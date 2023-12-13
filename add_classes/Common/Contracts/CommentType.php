<?php

declare(strict_types=1);

namespace App\Common\Contracts;

use ExportPortal\Enum\EnumCase;
use UnhandledMatchError;

/**
 * @method static self BLOGS()
 * @method static self NEWS()
 * @method static self UPDATES()
 * @method static self TRADE_NEWS()
 * @method static self EP_EVENTS()
 */
final class CommentType extends EnumCase
{
    public const BLOGS = 1;
    public const NEWS = 2;
    public const UPDATES = 3;
    public const TRADE_NEWS = 4;
    public const EP_EVENTS = 5;

    public function alias(): string
    {
        return static::getAlias($this);
    }

    public static function getAlias(self $value): string
    {
        switch ($value) {
            case static::BLOGS: return 'blogs';
            case static::NEWS: return 'news';
            case static::UPDATES: return 'updates';
            case static::TRADE_NEWS: return 'trade_news';
            case static::EP_EVENTS: return 'ep_events';
        }

        throw new UnhandledMatchError('The provided type is not supported.');
    }
}
