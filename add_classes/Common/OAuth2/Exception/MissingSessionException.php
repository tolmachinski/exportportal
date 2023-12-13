<?php

declare(strict_types=1);

namespace App\Common\OAuth2\Exception;

/**
 * The exception that is thrown when the session is missing in the request.
 */
class MissingSessionException extends \LogicException implements OAuth2Exception
{
    // Hic svnt dracones
}
