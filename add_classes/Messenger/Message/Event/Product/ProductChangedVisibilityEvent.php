<?php

declare(strict_types=1);

namespace App\Messenger\Message\Event\Product;

/**
 * Event triggered when item visible changed.
 */
final class ProductChangedVisibilityEvent extends AbstractProductEvent
{
    private bool $visible;

    public function __construct(int $productId, bool $visible)
    {
        parent::__construct($productId);

        $this->visible = $visible;
    }

    /**
     * This function returns the data of the product visible.
     */
    public function getVisible(): bool
    {
        return $this->visible;
    }

    /**
     * This function sets the new visible of the product.
     */
    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;

        return $this;
    }
}
