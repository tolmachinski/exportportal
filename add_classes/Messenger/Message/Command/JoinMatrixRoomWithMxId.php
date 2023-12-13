<?php

declare(strict_types=1);

namespace App\Messenger\Message\Command;

/**
 * Command that allows user to join into the room using user's matrix ID.
 *
 * @author Anton Zencenco
 */
final class JoinMatrixRoomWithMxId
{
    /**
     * The matrix user ID value.
     */
    private string $mxId;

    /**
     * The room ID.
     */
    private string $roomId;

    /**
     * The leave reason.
     */
    private ?string $reason;

    public function __construct(string $roomId, string $mxId, ?string $reason = null)
    {
        $this->mxId = $mxId;
        $this->roomId = $roomId;
        $this->reason = $reason;
    }

    /**
     * Get the matrix user ID value.
     */
    public function getMxId(): string
    {
        return $this->mxId;
    }

    /**
     * Set the matrix user ID value.
     *
     * @return $this
     */
    public function setMxId(string $mxId): self
    {
        $this->mxId = $mxId;

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
