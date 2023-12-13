<?php

declare(strict_types=1);

namespace App\Messenger\Message\Event\Matrix;

/**
 * Event triggered after the matrix cargo room for user was created.
 *
 * @author Anton Zencenco
 */
final class UserCargoRoomAddedEvent
{
    /**
     * The user ID value.
     */
    private int $userId;

    /**
     * The profile room id value.
     */
    private string $roomId;

    /**
     * @param int    $userId the user ID value
     * @param string $roomId the profile room ID value
     */
    public function __construct(int $userId, string $roomId)
    {
        $this->userId = $userId;
        $this->roomId = $roomId;
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
     * Get the profile room id value.
     */
    public function getRoomId(): string
    {
        return $this->roomId;
    }

    /**
     * Set the profile room id value.
     */
    public function setRoomId(string $roomId): self
    {
        $this->roomId = $roomId;

        return $this;
    }
}
