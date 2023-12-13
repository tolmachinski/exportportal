<?php

namespace App\Common\Exceptions;

use Throwable;

class B2bRequestNotFoundException extends NotFoundException
{
    /**
     * The b2b request ID.
     *
     * @var null|int
     */
    private $b2bRequestId;

    /**
     * {@inheritdoc}
     */
    public function __construct(?int $b2bRequestId = null, string $message = 'The b2b request with the provided ID is not found', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->b2bRequestId = $b2bRequestId;
    }

    /**
     * Get the b2b request ID.
     *
     * @return null|int
     */
    public function getB2bRequestId()
    {
        return $this->b2bRequestId;
    }

    /**
     * Set the b2b request ID.
     *
     * @param null|int $b2bRequestId the ID
     *
     * @return self
     */
    public function setB2bRequestId(?int $b2bRequestId)
    {
        $this->b2bRequestId = $b2bRequestId;

        return $this;
    }
}
