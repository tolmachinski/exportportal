<?php

declare(strict_types=1);

namespace App\Messenger\Message\Event;

/**
 * Event triggered when user account was created on the mastrix server.
 *
 * @author Anton Zencenco
 */
final class MatrixUserAddedEvent
{
    /**
     * The user ID value.
     */
    private int $userId;

    /**
     * @param int $userId the user ID value
     */
    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Get user ID value.
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * Set user ID value.
     */
    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }
}
