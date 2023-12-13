<?php

declare(strict_types=1);

namespace App\Common\Exceptions;

use App\Common\Database\Exceptions\DBException as OriginalDBException;

/**
 * @deprecated
 */
class DBException extends OriginalDBException
{
    // Here be dragons
}
