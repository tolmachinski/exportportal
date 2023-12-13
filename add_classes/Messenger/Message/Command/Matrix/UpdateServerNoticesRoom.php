<?php

declare(strict_types=1);

namespace App\Messenger\Message\Command\Matrix;

/**
 * Command that updates the user's server notices room.
 *
 * @author Anton Zencenco
 */
final class UpdateServerNoticesRoom
{
    /**
     * The user ID value.
     */
    private int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Get the user ID value.
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * Set the user ID value.
     */
    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }
}
