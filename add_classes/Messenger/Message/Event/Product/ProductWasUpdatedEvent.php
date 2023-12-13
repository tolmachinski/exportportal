<?php

declare(strict_types=1);

namespace App\Messenger\Message\Event\Product;

use Money\Currency;
use Money\Money;

/**
 * Event triggered when item changed.
 */
final class ProductWasUpdatedEvent extends AbstractProductEvent
{
    private array $changes;

    public function __construct(int $productId, array $changes = [])
    {
        parent::__construct($productId);

        $this->setChanges($changes);
    }

    /**
     * Get product changes.
     */
    public function getChanges(): ?array
    {
        return $this->changes;
    }

    /**
     * Set product changes.
     */
    public function setChanges(array $changes): self
    {
        if (isset($changes['price'])) {
            $changes['price'] = $this->normalizePrice($changes['price']);
        }
        if (isset($changes['final_price'])) {
            $changes['final_price'] = $this->normalizePrice($changes['final_price']);
        }
        $this->changes = $changes;

        return $this;
    }

    /**
     * Undocumented function.
     *
     * @param array|Money $price
     */
    private function normalizePrice($price): Money
    {
        if ($price instanceof Money) {
            return $price;
        }

        // If we get array here, it means that the serialized failed to properly decode
        // the Money\Money object. It meaans that we need to create it manually.
        if (\is_array($price)) {
            return new Money((string) $price['amount'], new Currency((string) $price['currency']));
        }

        throw new \InvalidArgumentException('The price format is not supported');
    }
}
