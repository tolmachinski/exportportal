<?php

views('new/item/filter_options/index_view', [
    'counterCategories' => $counterCategories ?? null,
    'searchCountries'   => $searchCountries ?? null,
    'country'           => $country,
    'mainCats'          => $mainCats,
    'returnToPage'      => 1,
]);
