<?php

declare(strict_types=1);

namespace App\Messenger\Message\Command;

/**
 * Command that deletes the room from matrix server.
 *
 * @author Anton Zencenco
 */
final class DeleteMatrixRoom
{
    /**
     * The room ID.
     */
    private string $roomId;

    /**
     * The new room name.
     *
     * Optional. A string representing the name of the room that new users will
     * be invited to. Defaults to `Content Violation Notification`
     */
    private ?string $newRoomName;

    /**
     * The new room administrator user ID.
     *
     * Optional. If set, a new room will be created with this user ID as the
     * creator and admin, and all users in the old room will be moved into
     * that room. If not set, no new room will be created and the users will
     * just be removed from the old room. The user ID must be on the local
     * server, but does not necessarily have to belong to a registered user
     */
    private ?string $newRoomUserId;

    /**
     * The delete reason message in the room.
     *
     * Optional. A string containing the first message that will be sent
     * as `new_room_user_id` in the new room. Ideally this will clearly
     * convey why the original room was shut down. Defaults to `Sharing
     * illegal content on this server is not permitted and rooms in
     * violation will be blocked`.
     */
    private ?string $newRoomMessage;

    /**
     * The flag that indicates if users can or cannot join old room.
     *
     * Optional. If set to true, this room will be added to a
     * blocking list, preventing future attempts to join the room.
     * Defaults to `false`
     */
    private bool $block;

    /**
     * The flag that indicates if room must be purged.
     *
     * Optional. If set to true, it will remove all traces of the
     * room from your database. Defaults to `true`.
     */
    private bool $purge;

    public function __construct(
        string $roomId,
        ?string $newRoomUserId = null,
        ?string $newRoomName = null,
        ?string $newRoomMessage = null,
        bool $block = false,
        bool $purge = false
    ) {
        $this->block = $block;
        $this->purge = $purge;
        $this->roomId = $roomId;
        $this->newRoomName = $newRoomName;
        $this->newRoomMessage = $newRoomMessage;
        $this->newRoomUserId = $newRoomUserId;
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
     */
    public function setRoomId(string $roomId): self
    {
        $this->roomId = $roomId;

        return $this;
    }

    /**
     * Get the new room name.
     */
    public function getNewRoomName(): ?string
    {
        return $this->newRoomName;
    }

    /**
     * Set the new room name.
     */
    public function setNewRoomName(?string $newRoomName): self
    {
        $this->newRoomName = $newRoomName;

        return $this;
    }

    /**
     * Get the new room administrator user ID.
     */
    public function getNewRoomUserId(): ?string
    {
        return $this->newRoomUserId;
    }

    /**
     * Set the new room administrator user ID.
     */
    public function setNewRoomUserId(?string $newRoomUserId): self
    {
        $this->newRoomUserId = $newRoomUserId;

        return $this;
    }

    /**
     * Get the delete reason message in the room.
     */
    public function getNewRoomMessage(): ?string
    {
        return $this->newRoomMessage;
    }

    /**
     * Set the delete reason message in the room.
     */
    public function setNewRoomMessage(?string $newRoomMessage): self
    {
        $this->newRoomMessage = $newRoomMessage;

        return $this;
    }

    /**
     * Get the flag that indicates if users can or cannot join old room.
     */
    public function getBlock(): bool
    {
        return $this->block;
    }

    /**
     * Set the flag that indicates if users can or cannot join old room.
     */
    public function setBlock(bool $block): self
    {
        $this->block = $block;

        return $this;
    }

    /**
     * Get the flag that indicates if room must be purged.
     */
    public function getPurge(): bool
    {
        return $this->purge;
    }

    /**
     * Set the flag that indicates if room must be purged.
     */
    public function setPurge(bool $purge): self
    {
        $this->purge = $purge;

        return $this;
    }
}
