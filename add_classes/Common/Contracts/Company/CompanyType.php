<?php

declare(strict_types=1);

namespace App\Common\Contracts\Company;

use ExportPortal\Enum\EnumCase;

/**
 * @author Anton Zencenco
 *
 * @method static self BRANCH()
 * @method static self COMPANY()
 */
final class CompanyType extends EnumCase
{
    public const BRANCH = 'branch';
    public const COMPANY = 'company';
}
