<?php

$blogsMapping = [
    'properties' => [
        'id'          => ['type' => 'long'],
        'id_category' => ['type' => 'long'],
        'id_country'  => ['type' => 'long'],
        'id_user'     => ['type' => 'long'],
        'lang'        => ['type' => 'text'],
        'title'       => [
            'type'   => 'text',
            'fields' => [
                'ngrams' => [
                    'type'            => 'text',
                    'analyzer'        => '2gramanalyzer',
                    'search_analyzer' => 'standard',
                ],
            ],
        ],
        'title_slug'        => ['type' => 'keyword'],
        'photo'             => ['type' => 'text'],
        'short_description' => [
            'type'   => 'text',
            'fields' => [
                'ngrams' => [
                    'type'            => 'text',
                    'analyzer'        => '2gramanalyzer',
                    'search_analyzer' => 'standard',
                ],
            ],
        ],
        'content' => [
            'type'   => 'text',
            'fields' => [
                'ngrams' => [
                    'type'            => 'text',
                    'analyzer'        => '2gramanalyzer',
                    'search_analyzer' => 'standard',
                ],
            ],
        ],
        'tags' => [
            'type'            => 'text',
            'analyzer'        => 'coma_analyzer',
            'search_analyzer' => 'keyword',
        ],
        'tags_uri' => [
            'type'            => 'text',
            'analyzer'        => 'coma_analyzer',
            'search_analyzer' => 'keyword',
        ],
        'date' => [
            'type'   => 'date',
            'format' => 'strict_date_optional_time||epoch_millis||yyyy-MM-dd HH:mm:ss',
        ],
        'publish_on' => [
            'type'   => 'date',
            'format' => 'yyyy-MM-dd',
        ],
        'published' => ['type' => 'byte'],
        'status'    => [
            'type' => 'keyword',
        ],
        'visible'     => ['type' => 'byte'],
        'author_type' => [
            'type' => 'keyword',
        ],
        'views'         => ['type' => 'long'],
        'fname'         => ['type' => 'text'],
        'lname'         => ['type' => 'text'],
        'category_name' => ['type' => 'text'],
        'category_url'  => ['type' => 'text'],
        'archive_date'  => [
            'type'   => 'date',
            'format' => 'MM-yyyy',
        ],
        'id_category_category_name' => [
            'type' => 'keyword',
        ],
        'suggest_autocomplete' => [
            'type'             => 'completion',
            'analyzer'         => 'autocomplete_analyzer',
            'max_input_length' => 100,
        ],
    ],
];
