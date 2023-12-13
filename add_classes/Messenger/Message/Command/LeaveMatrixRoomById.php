<?php

declare(strict_types=1);

namespace App\Messenger\Message\Command;

/**
 * Command that makes user to leave the room using user's local ID.
 *
 * @author Anton Zencenco
 */
final class LeaveMatrixRoomById
{
    /**
     * The user ID value.
     */
    private int $userId;

    /**
     * The room ID.
     */
    private string $roomId;

    /**
     * The leave reason.
     */
    private ?string $reason;

    public function __construct(string $roomId, int $userId, ?string $reason = null)
    {
        $this->userId = $userId;
        $this->roomId = $roomId;
        $this->reason = $reason;
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
     *
     * @return $this
     */
    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get the room ID.
     */
    public function getRoomId(): string
    {
        return $this->roomId;
    }

    /**
     * Set the room ID.
     *
     * @return $this
     */
    public function setRoomId(string $roomId): self
    {
        $this->roomId = $roomId;

        return $this;
    }

    /**
     * Get the leave reason.
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * Set the leave reason.
     *
     * @return $this
     */
    public function setReason(?string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }
}
