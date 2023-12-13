<?php

declare(strict_types=1);

namespace App\Messenger\Message\Event\Product;

/**
 * Generic product bus event.
 */
abstract class AbstractProductEvent
{
    /**
     * The product ID value.
     */
    protected int $productId;

    public function __construct(int $productId)
    {
        $this->productId = $productId;
    }

    /**
     * This function returns the productId of the item
     * 
     * @return int The productId
     */
    public function getProductId(): int
    {
        return $this->productId;
    }

    /**
     * This function returns the new price of the product
     */
    public function setProductId(int $productId): self
    {
        $this->productId = $productId;

        return $this;
    }
}
