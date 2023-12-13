<?php

declare(strict_types=1);

namespace App\Common\Http\Exceptions;

interface HttpExceptionInterface extends \Throwable
{
    /**
     * Returns the HTTP status code.
     */
    public function getStatusCode(): int;

    /**
     * Returns response headers.
     */
    public function getHeaders(): array;

    /**
     * Set the response headers.
     */
    public function setHeaders(array $headers): void;
}
