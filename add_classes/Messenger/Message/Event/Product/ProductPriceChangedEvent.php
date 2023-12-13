<?php

declare(strict_types=1);

namespace App\Messenger\Message\Event\Product;

use Money\Money;

/**
 * Event triggered when item price changed.
 */
final class ProductPriceChangedEvent extends AbstractProductEvent
{
    private Money $price;

    public function __construct(int $productId, Money $price)
    {
        parent::__construct($productId);

        $this->price = $price;
    }

    /**
     * This function returns the data of the product price.
     */
    public function getPrice(): Money
    {
        return $this->price;
    }

    /**
     * This function sets the new price of the product.
     */
    public function setNewPrice(Money $price): self
    {
        $this->price = $price;

        return $this;
    }
}
