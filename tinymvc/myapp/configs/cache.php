<?php

use const App\Common\CACHE_PATH;

return [
    'fastcache' => [
        // Name of the default cache pool
        'default' => 'app_files',

        //Global configurations
        'globals' => [
            'path' => CACHE_PATH,
        ],

        // List of defined pools
        'pools'   => [
            // Default file cache
            'app_files' => [
                'driver'  => 'Files',
                'psr16'   => true,
                'options' => [
                    'securityKey' => '/app/base',
                ],
            ],

            'encore' => [
                'driver'  => 'Memstatic',
            ],

            'epdocs' => [
                'driver'  => 'Files',
                'psr16'   => true,
                'options' => [
                    'securityKey' => '/app/epdocs',
                    'defaultTtl'  => 3600,
                ],
            ],

            'messenger' => [
                'driver'  => 'Files',
                'psr16'   => false,
                'options' => [
                    'path'       => CACHE_PATH . '/app/messenger',
                    'defaultTtl' => 0,
                ],
            ],

            'memory' => [
                'driver'  => 'Memstatic',
                'psr16'   => true,
                'options' => [
                    'defaultTtl'  => 3600,
                ],
            ],

            'companies' => [
                'driver'  => 'Files',
                'psr16'   => true,
                'options' => [
                    'securityKey' => '/app/companies',
                    'defaultTtl'  => 3600,
                ],
            ],
        ],
    ],
];
