<?php

declare(strict_types=1);

namespace App\Messenger\Message\Event;

/**
 * Event triggered after the matrix user E2E keys were created.
 *
 * @author Anton Zencenco
 */
final class MatrixUserKeysCreatedEvent
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
