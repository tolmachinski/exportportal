<?php

declare(strict_types=1);

namespace App\Messenger\Message\Command;

/**
 * Command that synchronises the user's account information on matrix server.
 *
 * @author Anton Zencenco
 */
final class SyncMatrixUser
{
    /**
     * The user ID value.
     */
    private int $userId;

    /**
     * The flag that indicates if sync is performed first time.
     */
    private bool $initialSync;

    public function __construct(int $userId, bool $initialSync = true)
    {
        $this->userId = $userId;
        $this->initialSync = $initialSync;
    }

    /**
     * Determine if if sync is performed first time.
     */
    public function isInitialSync(): bool
    {
        return $this->initialSync;
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

    /**
     * Get the flag that indicates if sync is performed first time.
     */
    public function getInitialSync(): bool
    {
        return $this->initialSync;
    }

    /**
     * Set the flag that indicates if sync is performed first time.
     */
    public function setInitialSync(bool $initialSync): self
    {
        $this->initialSync = $initialSync;

        return $this;
    }
}
