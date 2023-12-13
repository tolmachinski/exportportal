<?php

$faqMapping = [
    'properties' => [
        'id_faq'   => ['type' => 'integer'],
        'question' => [
            'type'   => 'text',
            'fields' => [
                'ngrams' => [
                    'type'     => 'text',
                    'analyzer' => 'search_items_analyzer',
                ],
            ],
        ],
        'answer' => [
            'type'   => 'text',
            'fields' => [
                'ngrams' => [
                    'type'     => 'text',
                    'analyzer' => 'search_items_analyzer',
                ],
            ],
        ],
        'faq_i18n' => [
            'type' => 'nested',
        ],
        'weight'       => ['type' => 'integer'],
        'top_priority' => ['type' => 'integer'],
        'tags'         => [
            'type'       => 'nested',
            'properties' => [
                'id'   => ['type' => 'integer'],
                'name' => ['type' => 'text'],
                'slug' => ['type' => 'keyword'],
            ],
        ],
    ],
];
