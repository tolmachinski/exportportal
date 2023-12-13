<?php

namespace App\Common\Traits\Items;

trait OptionsMakerTrait
{
    protected function makeItemOptions(array $groups = array(), array $combinations = array())
    {
        if (empty($groups)) {
            return array();
        }

        $filteredOptions = array();
        foreach ($groups as $groupKey => $group) {
            $filteredOptions['variant_groups'][$groupKey] = array(
                'group_name'  => $group['group_name'],
                'group_order' => (int) arrayGet($group, 'group_order'),
            );

            foreach ($group['variants'] as $optionKey => $option) {
                $filteredOptions['variant_groups'][$groupKey]['variants'][$optionKey] = $option;
            }
        }

        if (empty($combinations)) {
            return $filteredOptions;
        }

        foreach ($combinations as $combinationKey => &$combination) {
            ksort($combination['combination']);
            foreach ($combination['combination'] as $groupKey => &$combinationValue) {
                if (is_array($combinationValue)) {
                    ksort($combinationValue);
                }
            }

            $combination['price'] = moneyToDecimal(priceToUsdMoney($combination['price']));
            $filteredOptions['combinations'][$combinationKey] = $combination;
        }

        return $filteredOptions;
    }
}
