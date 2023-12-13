<?php

namespace App\Common\Exceptions;

use Throwable;

class UserNotFoundException extends NotFoundException
{
    /**
     * The user's ID.
     *
     * @var null|mixed
     */
    private $userId;

    /**
     * {@inheritdoc}
     */
    public function __construct($userId = null, string $message = 'The user with provided ID is not found', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->userId = $userId;
    }

    /**
     * Get the user's ID.
     *
     * @return null|mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set the user's ID.
     *
     * @param null|mixed $userId the user's ID
     *
     * @return self
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }
}
