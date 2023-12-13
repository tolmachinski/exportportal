<?php

declare(strict_types=1);

namespace App\Bridge\Matrix;

use Psr\Log\LogLevel;
use Throwable;

interface ExceptionHandlerInterface
{
    /**
     * Handles the exception.
     */
    public function handleException(Throwable $exception, ?string $message, array $context = [], string $level = LogLevel::ERROR): void;
}
