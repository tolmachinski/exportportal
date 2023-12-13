<?php

declare(strict_types=1);

namespace App\Common\Contracts\Blogs;

use ExportPortal\Enum\EnumCase;

/**
 * @method static self FRESH()
 * @method static self MODERATED()
 */
final class BlogsStatus extends EnumCase
{
    public const FRESH = 'new'; // do not use reserved word `new`
    public const MODERATED = 'moderated';
}
