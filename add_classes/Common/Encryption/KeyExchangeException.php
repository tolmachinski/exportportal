<?php

declare(strict_types=1);

namespace App\Common\Encryption;

use Throwable;

class KeyExchangeException extends \RuntimeException
{
    public function __construct(string $message = 'The key exchange failed.', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
