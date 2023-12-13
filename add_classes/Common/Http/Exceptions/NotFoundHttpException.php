<?php

declare(strict_types=1);

namespace App\Common\Http\Exceptions;

use Throwable;

class NotFoundHttpException extends HttpException
{
    /**
     * Creates instance of the HttpException.
     */
    public function __construct(?string $message = null, ?Throwable $previous = null, ?int $code = 0, array $headers = array())
    {
        parent::__construct(404, $message, $previous, $headers, $code);
    }
}
