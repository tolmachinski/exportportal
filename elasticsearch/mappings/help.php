<?php

$helpMapping = [
    'properties' => [
        'id'                    => [
            'type'  => 'text',
        ],
        'suggest_autocomplete'  => [
            'type'              => 'completion',
            'analyzer'          => 'autocomplete_analyzer',
            'max_input_length'  => 250,
        ],
    ],
];
