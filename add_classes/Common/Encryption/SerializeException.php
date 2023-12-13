<?php

declare(strict_types=1);

namespace App\Common\Encryption;

use Throwable;

class SerializeException extends \RuntimeException
{
    public function __construct(string $message = 'The serialization/deserialization of this instance is not allowed', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
