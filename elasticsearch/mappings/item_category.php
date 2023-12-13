<?php

$itemsCategoriesMapping = [
    'properties' => [
        'category_id'  => ['type' => 'long'],
        'parent'       => ['type' => 'long'],
        'has_children' => ['type' => 'boolean'],
        'has_vin'      => ['type' => 'boolean'],
        'name'         => [
            'type'      => 'text',
            'analyzer'  => 'categories_search_analyzer',
            'fielddata' => true,
        ],
        'name_to_lower' => [
            'type' => 'keyword',
        ],
        'slug' => [
            'type' => 'text',
        ],
        'completion' => [
            'type' => 'completion',
        ],
        'spellcheck' => [
            'type' => 'text',
        ],
        'breadcrumbs' => [
            'type' => 'text',
        ],
        'breadcrumbs_data' => [
            'type'          => 'nested',
            'properties'    => [
                'name'  => [
                    'type'  => 'text'
                ],
                'link'  => [
                    'type'  => 'text'
                ],
            ]

        ],
        'is_restricted' => [
            'type' => 'long',
        ],
        'suggest_autocomplete' => [
            'type'      => 'completion',
            'analyzer'  => 'category_suggester_analyzer',
        ],
    ],
];
