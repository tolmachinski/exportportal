<?php

declare(strict_types=1);

namespace App\Common\OAuth2\Exception;

/**
 * The exception that is thrown when the current request is missing from the request stack.
 */
class MissingCurrentrequestException extends \LogicException implements OAuth2Exception
{
    // Hic svnt dracones
}
