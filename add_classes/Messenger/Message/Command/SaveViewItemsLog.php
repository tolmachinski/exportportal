<?php

declare(strict_types=1);

namespace App\Messenger\Message\Command;

/**
 * Base command for saving view items log
 *
 */
final class SaveViewItemsLog
{
    /**
     * The item ID.
     */
    private int $itemId;
    /**
     * The user ID.
     */
    private ?int $userId;

    public function __construct(int $itemId, ?int $userId)
    {
        $this->itemId = $itemId;
        $this->userId = $userId;
    }

    /**
     * Get the user ID
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * Set the user ID value.
     *
     * @return $this
     */
    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get the item ID
     */
    public function getItemId(): int
    {
        return $this->itemId;
    }

    /**
     * Set the item ID value.
     *
     * @return $this
     */
    public function setItemId(int $itemId): self
    {
        $this->itemId = $itemId;

        return $this;
    }
}
