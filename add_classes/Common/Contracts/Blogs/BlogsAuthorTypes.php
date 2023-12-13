<?php

declare(strict_types=1);

namespace App\Common\Contracts\Blogs;

use ExportPortal\Enum\EnumCase;

/**
 * @method static self USER()
 * @method static self ADMIN()
 */
final class BlogsAuthorTypes extends EnumCase
{
    public const USER = 'user';
    public const ADMIN = 'admin';
}
