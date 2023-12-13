<?php

$citiesMapping = [
    'properties' => [
        'id'            => ['type' => 'long'],
        'name'          => [
            'type'              => 'text',
            'analyzer'          => 'help_search_analyzer',
            'search_analyzer'   => 'standard',
        ],
        'ascii_name'    => [
            'type'              => 'text',
            'analyzer'          => 'help_search_analyzer',
            'search_analyzer'   => 'standard',
        ],
        'timezone'      => ['type' => 'keyword'],
        'state'         => [
            'properties'    => [
                'ascii_name'    => ['type' => 'text'],
                'name'          => ['type' => 'text'],
                'code'          => ['type' => 'keyword'],
                'id'            => ['type' => 'long'],
            ],
        ],
        'country'       => [
            'properties'    => [
                'ascii_name'    => ['type' => 'text'],
                'alias'         => ['type' => 'keyword'],
                'abr3'          => ['type' => 'keyword'],
                'name'          => ['type' => 'text'],
                'abr'           => ['type' => 'keyword'],
                'id'            => ['type' => 'long'],
            ],
        ],
    ],
];
