<?php

$usersMapping = [
    'properties' => [
        'id'                => ['type' => 'integer'],
        'fname'             => [
            'type'      => 'text',
            'analyzer'  => 'users_analyzer'
        ],
        'lname'             => [
            'type'      => 'text',
            'analyzer'  => 'users_analyzer'
        ],
        'fullName'      => [
            'type'      => 'text',
            'analyzer'  => 'users_analyzer'
        ],
        'logged'            => ['type' => 'byte'],
        'is_verified'       => ['type' => 'byte'],
        'photo'             => ['type' => 'keyword'],
        'group'             => [
            'type' => 'nested',
            'properties'        => [
                'id'            => ['type' => 'integer'],
                'name'          => ['type' => 'keyword'],
                'alias'         => ['type' => 'keyword'],
                'type'          => ['type' => 'keyword'],
            ],
        ],
        'company'             => [
            'type' => 'nested',
            'properties'        => [
                'id'            => ['type' => 'integer'],
                'name'          => [
                    'type'      => 'text',
                    'analyzer'  => 'users_analyzer',
                ],
                'legalName'     => ['type' => 'text'],
            ],
        ],
    ],
];
