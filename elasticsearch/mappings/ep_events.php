<?php

$epEventsMapping = [
    'properties' => [
        'id'            => ['type' => 'long'],
        'id_type'       => ['type' => 'long'],
        'id_speaker'    => ['type' => 'long'],
        'id_category'   => ['type' => 'long'],
        'id_country'    => ['type' => 'long'],
        'id_state'      => ['type' => 'long'],
        'id_city'       => ['type' => 'long'],
        'title' => [
            'type'   => 'text',
            'fields' => [
                'ngrams' => [
                    'type'            => 'text',
                    'analyzer'        => '2gramanalyzer',
                    'search_analyzer' => 'standard',
                ],
            ],
        ],
        'start_date' => [
            'type'   => 'date',
            'format' => 'strict_date_optional_time||epoch_millis||yyyy-MM-dd HH:mm:ss',
        ],
        'end_date' => [
            'type'   => 'date',
            'format' => 'strict_date_optional_time||epoch_millis||yyyy-MM-dd HH:mm:ss',
        ],
        'create_date' => [
            'type'   => 'date',
            'format' => 'strict_date_optional_time||epoch_millis||yyyy-MM-dd HH:mm:ss',
        ],
        'update_date' => [
            'type'   => 'date',
            'format' => 'strict_date_optional_time||epoch_millis||yyyy-MM-dd HH:mm:ss',
        ],
        'highlighted_end_date' => [
            'type'   => 'date',
            'format' => 'strict_date_optional_time||epoch_millis||yyyy-MM-dd HH:mm:ss',
        ],
        'published_date' => [
            'type'   => 'date',
            'format' => 'strict_date_optional_time||epoch_millis||yyyy-MM-dd HH:mm:ss',
        ],
        'description' => [
            'type'   => 'text',
            'fields' => [
                'ngrams' => [
                    'type'            => 'text',
                    'analyzer'        => '2gramanalyzer',
                    'search_analyzer' => 'standard',
                ],
            ],
        ],
        'short_description' => [
            'type' => 'text',
            'fields' => [
                'ngrams' => [
                    'type'            => 'text',
                    'analyzer'        => '2gramanalyzer',
                    'search_analyzer' => 'standard',
                ],
            ],
        ],
        'why_attend' => [
            'type'   => 'text',
            'fields' => [
                'ngrams' => [
                    'type'            => 'text',
                    'analyzer'        => '2gramanalyzer',
                    'search_analyzer' => 'standard',
                ],
            ],
        ],
        'agenda' => [
            'type' => 'nested',
            'properties'    => [
                'startDate'     => [
                    'type'   => 'date',
                    'format' => 'strict_date_optional_time||epoch_millis||MM/dd/yyyy HH:mm',
                ],
                'description'   => ['type' => 'text'],
            ],
        ],
        'main_image'           => ['type' => 'text'],
        'recommended_image'    => ['type' => 'text'],
        'address'              => ['type' => 'text'],
        'url'                  => ['type' => 'text'],
        'ticket_price'         => ['type' => 'long'],
        'nr_of_participants'   => ['type' => 'long'],
        'is_recommended_by_ep' => ['type' => 'byte'],
        'is_attended_by_ep'    => ['type' => 'byte'],
        'is_upcoming_by_ep'    => ['type' => 'byte'],
        'views'                => ['type' => 'long'],
        'is_published'         => ['type' => 'byte'],
        'gallery'              => [
            'type'          => 'nested',
            'properties'    => [
                'id'       => ['type' => 'long'],
                'id_event' => ['type' => 'long'],
                'name'     => ['type' => 'text'],
            ],
        ],
        'partners' => [
            'type'          => 'nested',
            'properties'    => [
                'id'    => ['type' => 'long'],
                'image' => ['type' => 'text'],
                'name'  => ['type' => 'text'],
            ],
        ],
        'speaker' => [
            'type'              => 'nested',
            'properties'        => [
                'id'        => ['type' => 'long'],
                'name'      => ['type' => 'text'],
                'photo'     => ['type' => 'text'],
                'position'  => ['type' => 'text'],
            ],
        ],
        'type' => [
            'type'          => 'nested',
            'properties'    => [
                'id'        => ['type' => 'long'],
                'alias'     => ['type' => 'text'],
                'title'     => ['type' => 'text'],
                'slug'      => ['type' => 'keyword'],
            ],
        ],
        'category' => [
            'type'          => 'nested',
            'properties'    => [
                'id'            => ['type' => 'long'],
                'url'           => ['type' => 'keyword'],
                'name'          => ['type' => 'text'],
                'special_link'  => ['type' => 'keyword'],
            ],
        ],
        'country' => [
            'type'          => 'nested',
            'properties'    => [
                'id'        => ['type' => 'long'],
                'name'      => ['type' => 'text'],
                'slug'      => ['type' => 'keyword']
            ],
        ],
        'state' => [
            'type'          => 'nested',
            'properties'    => [
                'id'        => ['type' => 'long'],
                'name'      => ['type' => 'text'],
            ],
        ],
        'city' => [
            'type'          => 'nested',
            'properties'    => [
                'id'        => ['type' => 'long'],
                'name'      => ['type' => 'text'],
            ],
        ],
        'tags' => [
            'type'              => 'text',
            'analyzer'          => 'coma_analyzer',
            'search_analyzer'   => 'keyword',
        ],
        'suggest_autocomplete' => [
            'type'              => 'completion',
            'analyzer'          => 'autocomplete_analyzer',
            'max_input_length'  => 250,
        ],
    ],
];
