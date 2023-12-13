<?php

declare(strict_types=1);

namespace App\Common\OAuth2\Exception;

/**
 * The exception that is thrown the authorization code is missing from request.
 */
class MissingAuthorizationCodeException extends \RuntimeException implements OAuth2Exception
{
    // Hic svnt dracones
}
