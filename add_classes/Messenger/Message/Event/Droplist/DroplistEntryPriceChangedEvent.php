<?php

declare(strict_types=1);

namespace App\Messenger\Message\Event\Droplist;

use Money\Money;

/**
 * Event triggered when droplist entry price waschanged.
 */
final class DroplistEntryPriceChangedEvent
{
    /**
     * The ID of the droplist entry.
     */
    private int $id;

    /**
     * The new price of the droplist entry.
     */
    private ?Money $price;

    /**
     * @param int   $id    the ID of the droplist entry
     * @param Money $price the new price of the droplist entry
     */
    public function __construct(int $id, ?Money $price = null)
    {
        $this->id = $id;
        $this->price = $price;
    }

    /**
     * Get the ID of the droplist entry.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set the ID of the droplist entry.
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the new price of the droplist entry.
     */
    public function getPrice(): ?Money
    {
        return $this->price;
    }

    /**
     * Set the new price of the droplist entry.
     */
    public function setPrice(?Money $price): self
    {
        $this->price = $price;

        return $this;
    }
}
