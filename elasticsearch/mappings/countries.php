<?php

$countriesMapping = [
    'properties' => [
        'id' => [
            'type' => 'long'
        ],
        'name' => [
            'type'   => 'text',
        ],
        'ascii_name' => [
            'type'   => 'text',
        ],
        'alias' => [
            'type' => 'keyword'
        ],
        'phone_code'  => [
            'properties'    => [
                'id'    => [
                    'type' => 'long'
                ],
                'ccode' => [
                    'type' => 'text'
                ],
                'phone_pattern_international_mask'  => [
                    'type'  => 'text',
                    'index' => false,
                ],
                'phone_pattern_general' => [
                    'type'  => 'text',
                    'index' => false,
                ],
            ],
        ],
        'abr' => [
            'type' => 'keyword',
        ],
        'abr3' => [
            'type' => 'keyword',
        ],
        'location' => [
            'type' => 'geo_point',
        ],
        'continent' => [
            'properties' => [
                'id' => [
                    'type' => 'byte',
                ],
                'name' => [
                    'type' => 'keyword',
                ],
            ],
        ],
        'is_focus_country' => [
            'type' => 'byte',
        ],
        'position_on_select' => [
            'type' => 'byte',
        ],
    ],
];
