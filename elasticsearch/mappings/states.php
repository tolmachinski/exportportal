<?php

$statesMapping = [
    'properties' => [
        'id'            => ['type' => 'long'],
        'name'          => ['type' => 'text'],
        'ascii_name'    => ['type' => 'text'],
        'code'          => ['type' => 'keyword'],
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
