<?php

declare(strict_types=1);

namespace App\Common\Http\Exceptions;

use Throwable;

class HttpException extends \RuntimeException implements HttpExceptionInterface
{
    /**
     * The HTTP status code.
     *
     * @var int
     */
    private $statusCode;

    /**
     * The list of http headers.
     *
     * @var array
     */
    private $headers;

    /**
     * Creates instance of the HttpException.
     */
    public function __construct(int $statusCode, ?string $message = null, ?Throwable $previous = null, array $headers = array(), ?int $code = 0)
    {
        $this->headers = $headers;
        $this->statusCode = $statusCode;
        parent::__construct($message, $code, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }
}
