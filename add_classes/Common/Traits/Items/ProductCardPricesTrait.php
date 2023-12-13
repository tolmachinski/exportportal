<?php

namespace App\Common\Traits\Items;

use Money\Money;

trait ProductCardPricesTrait
{
    private int $moneyCondition = 1000000;

    /**
     * Format price for Product card.
     */
    public function formatProductPrice(array $items = []): array
    {
        if (empty($items)) {
            return [];
        }

        foreach ($items as &$item) {
            foreach ($item['card_prices'] ?: [] as $key => $cardPrice) {
                if (null === $cardPrice) {
                    unset($item['card_prices'][$key]);
                    continue;
                }

                $item['card_prices'][$key] = !$item['has_variants'] || priceToUsdMoney($cardPrice)->lessThan(Money::USD($this->moneyCondition)) ? get_price($cardPrice) : substr(get_price($cardPrice), 0, -3);
            }
        }

       return $items;
    }
}
