<?php

return [
    'main' => [
        'file_path'        => 'public/storage/company/{ID}/logo/{FILE_NAME}',
        'folder_path'      => 'var/app/public/company/{ID}/logo/',
        'temp_folder_path' => 'temp/company/{ID}/{FOLDER}/logo/',
        'resize'           => [
            'width'  => 280,
            'height' => 'R',
        ],
        'rules' => [
            'min_width'        => 280,
            'min_height'       => 280,
            'format'           => 'jpg,jpeg,png,gif,bmp',
            'size'             => 10 * 1024 * 1024,
            'size_placeholder' => '10MB',
        ],
        'thumbs' => [
            0 => [
                'name' => 'thumb_0_{THUMB_NAME}',
                'w'    => 80,
                'h'    => 'R',
            ],
            1 => [
                'name' => 'thumb_1_{THUMB_NAME}',
                'w'    => 140,
                'h'    => 'R',
            ],
        ],
    ],
    'photos' => [
        'file_path'        => 'public/storage/company/{ID}/pictures/{FILE_NAME}',
        'folder_path'      => 'var/app/public/company/{ID}/pictures/',
        'temp_folder_path' => 'temp/company/{ID}/{FOLDER}/pictures/',
        'limit'            => 15,
        'resize'           => [
            'width'  => 800,
            'height' => 'R',
        ],
        'rules' => [
            'min_width'        => 800,
            'min_height'       => 600,
            'format'           => 'jpg,jpeg,png,gif,bmp',
            'size'             => 10 * 1024 * 1024,
            'size_placeholder' => '10MB',
            'ratio'            => 2.5,
        ],
        'thumbs' => [
            1 => [
                'name' => 'thumb_1_{THUMB_NAME}',
                'w'    => 140,
                'h'    => 'R',
            ],
            2 => [
                'name' => 'thumb_2_{THUMB_NAME}',
                'w'    => 220,
                'h'    => 'R',
            ],
        ],
    ],
];
