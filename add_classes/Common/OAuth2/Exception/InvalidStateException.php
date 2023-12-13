<?php

declare(strict_types=1);

namespace App\Common\OAuth2\Exception;

/**
 * The exception that is thrown when the OAuth2 state is invalid.
 */
class InvalidStateException extends \RuntimeException implements OAuth2Exception
{
    // Hic svnt dracones
}
