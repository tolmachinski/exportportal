<?php

declare(strict_types=1);

namespace App\Messenger\Message\Command;

/**
 * Command that deactivates the user's account on the matrix server by user local ID.
 *
 * @author Anton Zencenco
 */
final class DeactivateKnownMatrixUser
{
    /**
     * The user ID value.
     */
    private int $userId;

    /**
     * @param int $userId the sync reference ID value
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
