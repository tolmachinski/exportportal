<?php

$itemsMapping = [
    'properties' => [
        'id'         => ['type' => 'long'],
        'views'      => ['type' => 'integer'],
        'total_sold' => ['type' => 'integer'],
        'id_seller'  => ['type' => 'integer'],
        'id_cat'     => ['type' => 'integer'],
        'industryId' => ['type' => 'integer'],
        'title'      => [
            'type'     => 'text',
            'analyzer' => 'search_items_analyzer',
            'boost'    => 20,
        ],
        'suggest_autocomplete' => [
            'type'             => 'completion',
            'analyzer'         => 'autocomplete_analyzer',
            'max_input_length' => 100,
        ],
        'tags'                   => [
            'type'      => 'text',
            'analyzer'  => 'items_tags_analyzer',
            'fielddata' => true,
            'boost'    => 3,
        ],
        'year'                   => ['type' => 'short'],
        'price'                  => ['type' => 'double'],
        'discount'               => ['type' => 'short'],
        'final_price'            => ['type' => 'double'],
        'card_prices'            => [
            'type'          => 'nested',
            'properties'    => [
                'min_price'         => ['type' => 'double'],
                'max_price'         => ['type' => 'double'],
                'min_final_price'   => ['type' => 'double'],
                'max_final_price'   => ['type' => 'double'],
            ],
        ],
        'weight'                 => ['type' => 'float'],
        'item_length'            => ['type' => 'float'],
        'item_width'             => ['type' => 'float'],
        'item_height'            => ['type' => 'float'],
        'quantity'               => ['type' => 'integer'],
        'min_sale_q'             => ['type' => 'integer'],
        'unit_type'              => ['type' => 'short'],
        'create_date'            => [
            'type'   => 'date',
            'format' => 'strict_date_optional_time||epoch_millis||yyyy-MM-dd HH:mm:ss',
        ],
        'update_date' => [
            'type'   => 'date',
            'format' => 'strict_date_optional_time||epoch_millis||yyyy-MM-dd HH:mm:ss',
        ],
        'expire_date' => [
            'type'   => 'date',
            'format' => 'strict_date_optional_time||epoch_millis||yyyy-MM-dd HH:mm:ss',
        ],
        'featured_from_date' => [
            'type'             => 'date',
            'format'           => 'strict_date_optional_time||epoch_millis||yyyy-MM-dd HH:mm:ss',
            'ignore_malformed' => true,
        ],
        'country_name'      => ['type' => 'keyword'],
        'p_country'         => ['type' => 'integer'],
        'origin_country'    => ['type' => 'integer'],
        'p_city'            => ['type' => 'integer'],
        'state'             => ['type' => 'integer'],
        'description'       => [
            'type'     => 'text',
            'analyzer' => 'search_items_analyzer',
        ],
        'search_info' => [
            'type'     => 'text',
            'analyzer' => 'search_items_analyzer',
        ],
        'company_info' => [
            'type'     => 'text',
            'analyzer' => 'search_items_analyzer',
        ],
        'offers'                          => ['type' => 'byte'],
        'samples'                         => ['type' => 'byte'],
        'order_now'                       => ['type' => 'byte'],
        'featured'                        => ['type' => 'byte'],
        'is_out_of_stock'                 => ['type' => 'byte'],
        'rating'                          => ['type' => 'float'],
        'rev_numb'                        => ['type' => 'integer'],
        'highlight'                       => ['type' => 'byte'],
        'photo_name'                      => ['type' => 'text'],
        'photo_thumbs'                    => ['type' => 'text'],
        'accreditation'                   => ['type' => 'byte'],
        'item_categories'                 => [
            'type'      => 'text',
            'analyzer'  => 'pattern',
            'fielddata' => true,
            'fields'    => [
                'path' => [
                    'type'            => 'text',
                    'analyzer'        => 'path-analyzer',
                    'search_analyzer' => 'keyword',
                    'fielddata'       => true,
                ],
            ],

        ],
        'is_restricted'                 => ['type' => 'byte'],
        'item_attr_select'              => [
            'type'      => 'text',
            'analyzer'  => 'pattern',
            'fielddata' => true,
        ],
        'has_variants'                  => ['type' => 'byte'],
        'properties'                    => [
            'type'          => 'nested',
            'properties'    => [
                'id'                => ['type' => 'integer'],
                'name'              => ['type' => 'text'],
                'priority'          => ['type' => 'byte'],
                'options'           => [
                    'type'          => 'nested',
                    'properties'    => [
                        'id'        => ['type' => 'integer'],
                        'name'      => ['type' => 'text'],
                    ],
                ],
            ],
        ],
        'variants'                      => [
            'type'          => 'nested',
            'properties'    => [
                'id'            => ['type' => 'integer'],
                'price'         => ['type' => 'double'],
                'final_price'   => ['type' => 'double'],
                'discount'      => ['type' => 'byte'],
                'quantity'      => ['type' => 'integer'],
                'image'         => ['type' => 'keyword'],
                'options'       => [
                    'type'          => 'nested',
                    'properties'    => [
                        'id'            => ['type' => 'integer'],
                        'name'          => ['type' => 'text'],
                        'property_name' => ['type' => 'text'],
                        'property_id'   => ['type' => 'integer'],
                    ],
                ],
            ],
        ],
        'is_handmade'                      => ['type' => 'byte'],
    ],
    'dynamic_templates' => [
        [
            'attr_text' => [
                'match'   => 'attr_text_*',
                'mapping' => [
                    'type' => 'text',
                ],
            ],
        ],
    ],
];
