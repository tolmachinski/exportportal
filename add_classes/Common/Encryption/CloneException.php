<?php

declare(strict_types=1);

namespace App\Common\Encryption;

use Throwable;

final class CloneException extends \RuntimeException
{
    public function __construct(string $message = 'The cloning of this instance is not allowed', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
