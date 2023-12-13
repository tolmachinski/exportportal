<?php

namespace App\Common\Exceptions;

use Throwable;

class BadEmailException extends AccessDeniedException
{
    /**
     * The response recieved from metadata provider.
     *
     * @var null|mixed
     */
    private $response;

    /**
     * {@inheritdoc}
     *
     * @param mixed $response
     */
    public function __construct(string $message = '', $response = null, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->response = $response;
    }

    /**
     * Returns the response.
     *
     * @return null|mixed
     */
    public function getResponse()
    {
        return $this->response;
    }
}
