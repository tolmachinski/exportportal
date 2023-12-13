<?php

declare(strict_types=1);

namespace App\Common\Contracts\Locale;

use ExportPortal\Enum\EnumCase;

/**
 * @author Anton Zencenco
 *
 * @method static self DOMAIN()
 * @method static self URL_QUERY()
 * @method static self GOOGLE_HASH()
 */
final class LocaleUrlType extends EnumCase
{
    public const DOMAIN = 'domain';
    public const URL_QUERY = 'get_variable';
    public const GOOGLE_HASH = 'google_hash';
}
