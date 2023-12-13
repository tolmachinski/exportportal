<?php

$b2bRequestsMapping = [
    'properties' => [
        'id'                => ['type' => 'integer'],
        'companyId'         => ['type' => 'integer'],
        'userId'            => ['type' => 'integer'],
        'radius'            => ['type' => 'short'],
        'title'             => [
            'type'   => 'text',
            'fields' => [
                'ngrams' => [
                    'type'            => 'text',
                    'analyzer'        => '2gramanalyzer',
                    'search_analyzer' => 'standard',
                ],
            ],
        ],
        'message'           => [
            'type'   => 'text',
            'fields' => [
                'ngrams' => [
                    'type'            => 'text',
                    'analyzer'        => '2gramanalyzer',
                    'search_analyzer' => 'standard',
                ],
            ],
        ],
        'tags'              => [],
        'registerDate'      => [
            'type'   => 'date',
            'format' => 'strict_date_optional_time||epoch_millis||yyyy-MM-dd HH:mm:ss',
        ],
        'updateDate'        => [
            'type'   => 'date',
            'format' => 'strict_date_optional_time||epoch_millis||yyyy-MM-dd HH:mm:ss',
        ],
        'categories'        => [
            'type'              => 'nested',
            'properties'        => [
                'id'            => ['type' => 'integer'],
                'name'          => ['type' => 'text'],
                'parent'        => ['type' => 'integer'],
                'industryId'    => ['type' => 'integer'],
            ],
        ],
        'industries'        => [
            'type'          => 'nested',
            'properties'    => [
                'id'    => ['type' => 'integer'],
                'name'  => ['type' => 'text'],
            ],
        ],
        'photos'              => [
            'type' => 'nested',
            'properties'    => [
                'name' => ['type' => 'text']
            ]
        ],
        'mainImage'           => ['type' => 'text'],
        'countViews'          => ['type' => 'integer'],
        'countAdvices'        => ['type' => 'integer'],
        'countries'           => [
            'type'          => 'nested',
            'properties'    => [
                'id'        => ['type' => 'integer'],
                'name'      => ['type' => 'text'],
            ],
        ],
        'typeLocation'      => ['type' => 'text'],
        'company'           => [
            'type'          => 'nested',
            'properties'    => [
                'id'                => ['type' => 'integer'],
                'displayedName'     => ['type' => 'text'],
                'legalName'         => ['type' => 'text'],
                'indexName'         => ['type' => 'text'],
                'type'              => ['type' => 'keyword'],
                'latitude'          => ['type' => 'keyword'],
                'longitude'         => ['type' => 'keyword'],
                'parent'            => ['type' => 'integer'],
                'logo'              => ['type' => 'keyword'],
                'address'           => ['type' => 'text'],
                'zip'               => ['type' => 'keyword'],
                'country'           => [
                    'type'          => 'nested',
                    'properties'    => [
                        'id'        => ['type' => 'integer'],
                        'name'      => ['type' => 'text'],
                        'slug'      => ['type' => 'keyword'],
                    ],
                ],
                'state' => [
                    'type'          => 'nested',
                    'properties'    => [
                        'id'        => ['type' => 'integer'],
                        'name'      => ['type' => 'text'],
                    ],
                ],
                'city' => [
                    'type'          => 'nested',
                    'properties'    => [
                        'id'        => ['type' => 'integer'],
                        'name'      => ['type' => 'text'],
                    ],
                ],
            ],
        ],
        'partnerType'       => [
            'type'          => 'nested',
            'properties'    => [
                'id'        => ['type' => 'short'],
                'name'      => ['type' => 'keyword'],
            ],
        ],
        'suggest_autocomplete' => [
            'type'              => 'completion',
            'analyzer'          => 'b2b_suggester_analyzer',
            'max_input_length'  => 255
        ],
    ],
];
