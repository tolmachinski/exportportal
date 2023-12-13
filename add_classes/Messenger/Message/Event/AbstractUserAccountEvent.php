<?php

declare(strict_types=1);

namespace App\Messenger\Message\Event;

/**
 * Base event for any event that represents the user state and/or activity changes.
 *
 * @author Anton Zencenco
 */
abstract class AbstractUserAccountEvent
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
