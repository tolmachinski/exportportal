<?php

declare(strict_types=1);

namespace App\Common\Transformers;

use Items_Variants_Model;
use TinyMVC_Library_Elasticsearch;
use League\Fractal\TransformerAbstract;
use App\Common\Traits\Elasticsearch\AutocompleteAnalyzerTrait;

class ItemsToElasticsearchTransformer extends TransformerAbstract
{
    use AutocompleteAnalyzerTrait;

    /** @var Elasticsearch_Items_Model $elasticStorage */
    private TinyMVC_Library_Elasticsearch $elasticsearchLibrary;

    /** @var Items_Variants_Model $itemVariantsModel */
    private Items_Variants_Model $itemVariantsModel;

    private string $type = "items";

    public function __construct(TinyMVC_Library_Elasticsearch $elasticsearchLibrary, Items_Variants_Model $itemVariantsModel)
    {
        $this->elasticsearchLibrary = $elasticsearchLibrary;
        $this->itemVariantsModel = $itemVariantsModel;
    }

    public function transform(array $item): array
    {
        if (!empty($item['item_attr_range'])) {
            $range_attrs = explode('|~%~|', $item['item_attr_range']);
            foreach ($range_attrs as $range_attr) {
                $range_attr_components = explode('-', $range_attr, 2);
                $item["{$range_attr_components[0]}"] = (float) $range_attr_components[1];
            }
        }
        unset($item['item_attr_range']);

        if (!empty($item['item_attr_text'])) {
            $text_attrs = explode('|~%~|', $item['item_attr_text']);
            foreach ($text_attrs as $text_attr) {
                $text_attr_components = explode('-', $text_attr, 2);
                $item["{$text_attr_components[0]}"] = $text_attr_components[1];
            }
        }
        unset($item['item_attr_text']);

        /* Start Autocomplete suggestions */
        $tokens = $this->analyzeAutocompleteText($item['title']);
        if (!empty($tokens['tokens'])) {
            foreach ($tokens['tokens'] as $token) {
                $item['suggest_autocomplete'][] = [
                    'input'  => $token['token'],
                    'weight' => 5,
                ];
            }
        }

        $item['suggest_autocomplete'][] = [
            'input'  => strtolower($item['title']),
            'weight' => 4,
        ];

        $categoriesNames = explode('|', $item['item_categories_names'] ?: '|');
        foreach ($categoriesNames as $key => $categoryName) {
            if ($key === array_key_last($categoriesNames)) {
                $item['suggest_autocomplete'][] = [
                    'input'  => strtolower($categoryName),
                    'weight' => 3,
                ];
            } else {
                $item['suggest_autocomplete'][] = [
                    'input'  => strtolower($categoryName),
                    'weight' => 2,
                ];
            }
        }
        unset($item['item_categories_names']);

        $item['tags'] = (array) explode(',', $item['tags'] ?: '');
        foreach ($item['tags'] as $tag) {
            $item['suggest_autocomplete'][] = [
                'input'  => strtolower($tag),
                'weight' => 1,
            ];
        }
        /* End Autocomplete suggestions */

        $item['variants'] = [];
        $item['properties'] = [];
        $item['card_prices'] = [
            'min_final_price' => null,
            'max_final_price' => null,
            'min_price'       => null,
            'max_price'       => null,
        ];

        if ($item['has_variants']) {
            $itemVariants = $this->itemVariantsModel->getItemVariants((int) $item['id']);

            foreach ((array) $itemVariants['properties'] as $property) {
                $propertyOptions = [];
                foreach ($property['property_options']->toArray() as $option) {
                    $propertyOptions[] = [
                        'id'    => (int) $option['id'],
                        'name'  => $option['name'],
                    ];
                }

                $item['properties'][] = [
                    'id'        => (int) $property['id'],
                    'name'      => $property['name'],
                    'priority'  => (int) $property['priority'],
                    'options'   => $propertyOptions,
                ];
            }

            $minPrice = $maxPrice = $minFinalPrice = $maxFinalPrice = null;
            foreach ((array) $itemVariants['variants'] as $variant) {
                $variantOptions = [];

                foreach ($variant['property_options']->toArray() as $option) {
                    $variantOptions[] = [
                        'property_name' => $option['propertyName'],
                        'property_id'   => (int) $option['id_property'],
                        'name'          => $option['name'],
                        'id'            => (int) $option['id'],
                    ];
                }

                $item['variants'][] = [
                    'id'            => (int) $variant['id'],
                    'price'         => moneyToDecimal($variant['price']),
                    'final_price'   => moneyToDecimal($variant['final_price']),
                    'discount'      => (int) $variant['discount'],
                    'quantity'      => (int) $variant['quantity'],
                    'image'         => (int) $variant['image'],
                    'options'       => $variantOptions,
                ];

                if (null === $minFinalPrice || $variant['final_price']->lessThan($minFinalPrice)) {
                    $minFinalPrice = $variant['final_price'];
                }

                if (null === $maxFinalPrice || $variant['final_price']->greaterThan($maxFinalPrice)) {
                    $maxFinalPrice = $variant['final_price'];
                }

                if (!empty($variant['discount'])) {
                    if (null === $minPrice || $variant['price']->lessThan($minPrice)) {
                        $minPrice = $variant['price'];
                    }

                    if (null === $maxPrice || $variant['price']->greaterThan($maxPrice)) {
                        $maxPrice = $variant['price'];
                    }
                }
            }

            if (!empty($minFinalPrice)) {
                $minFinalPrice = (float) moneyToDecimal($minFinalPrice);
                $item['card_prices']['min_final_price'] = (int) ($minFinalPrice / 10_000) >= 1 ? (int) $minFinalPrice : (float) $minFinalPrice;
            }

            if (!empty($maxFinalPrice)) {
                $maxFinalPrice = (float) moneyToDecimal($maxFinalPrice);
                if ($maxFinalPrice != $minFinalPrice) {
                    $item['card_prices']['max_final_price'] = (int) ($maxFinalPrice / 10_000) >= 1 ? (int) $maxFinalPrice : (float) $maxFinalPrice;
                }
            }

            if (!empty($minPrice)) {
                $minPrice = (float) moneyToDecimal($minPrice);
                $item['card_prices']['min_price'] = (int) ($minPrice / 10_000) >= 1 ? (int) $minPrice : (float) $minPrice;
            }

            if (!empty($maxPrice)) {
                $maxPrice = (float) moneyToDecimal($maxPrice);
                if ($maxPrice != $minPrice) {
                    $item['card_prices']['max_price'] = (int) ($maxPrice / 10_000) >= 1 ? (int) $maxPrice : (float) $maxPrice;
                }
            }
        } else {
            $item['card_prices']['min_final_price'] = (float) $item['final_price'];

            if (!empty($item['discount'])) {
                $item['card_prices']['min_price'] = (float) $item['price'];
            }
        }

        return [
            'id'                   => $item['id'],
            'views'                => $item['views'],
            'total_sold'           => $item['total_sold'],
            'id_seller'            => $item['id_seller'],
            'id_cat'               => $item['id_cat'],
            'industryId'           => $item['industryId'],
            'title'                => $item['title'],
            'suggest_autocomplete' => $item['suggest_autocomplete'],
            'tags'                 => $item['tags'],
            'year'                 => $item['year'],
            'price'                => $item['price'],
            'discount'             => $item['discount'],
            'final_price'          => $item['final_price'],
            'card_prices'          => $item['card_prices'],
            'weight'               => $item['weight'],
            'item_length'          => $item['item_length'],
            'item_width'           => $item['item_width'],
            'item_height'          => $item['item_height'],
            'quantity'             => $item['quantity'],
            'min_sale_q'           => $item['min_sale_q'],
            'unit_type'            => $item['unit_type'],
            'create_date'          => $item['create_date'],
            'update_date'          => $item['update_date'],
            'expire_date'          => $item['expire_date'],
            'featured_from_date'   => $item['featured_from_date'],
            'country_name'         => $item['country_name'],
            'p_country'            => $item['p_country'],
            'origin_country'       => $item['origin_country'],
            'p_city'               => $item['p_city'],
            'state'                => $item['state'],
            'description'          => $item['description'],
            'search_info'          => $item['search_info'],
            'company_info'         => $item['company_info'],
            'offers'               => $item['offers'],
            'samples'              => $item['samples'],
            'order_now'            => $item['order_now'],
            'featured'             => $item['featured'],
            'is_out_of_stock'      => $item['is_out_of_stock'],
            'rating'               => $item['rating'],
            'rev_numb'             => $item['rev_numb'],
            'highlight'            => $item['highlight'],
            'photo_name'           => $item['photo_name'],
            'photo_thumbs'         => $item['photo_thumbs'],
            'accreditation'        => $item['accreditation'],
            'item_categories'      => $item['item_categories'],
            'is_restricted'        => $item['is_restricted'],
            'item_attr_select'     => $item['item_attr_select'],
            'has_variants'         => $item['has_variants'],
            'properties'           => $item['properties'],
            'variants'             => $item['variants'],
            'is_handmade'          => $item['is_handmade'],
            'origin_country_name'  => $item['origin_country_name']
        ];
    }
}
