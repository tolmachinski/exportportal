<?php return [
    'main' => [
        'file_path'         => "public/storage/ep_events/images/{ID}/{FILE_NAME}",
        'folder_path'       => "public/storage/ep_events/images/{ID}/",
        'temp_folder_path'  => "temp/ep_events/{ENCRYPTED_FOLDER_NAME}/main/",
        'aspect_ratio'      => 2.3,
        'limit'             => 1,
        'resize'      => [
            'width' => 1296,
            'height' => 'R'
        ],
        'rules' => [
            'min_width' => 1300,
            'min_height' => 565,
            'format' => 'jpg,jpeg,png',
            'size' => 10 * 1024 * 1024,
            'size_placeholder' => '10MB',
        ],
        'thumbs' => [
            0 => [
                'name' => 'thumb_0_{THUMB_NAME}',
                'w' => 465,
                'h' => 'R'
            ],
            1 => [
                'name' => 'thumb_1_{THUMB_NAME}',
                'w' => 580,
                'h' => 'R'
            ]
        ]
    ],
    'gallery' => [
        'file_path'         => "public/storage/ep_events/images/{ID}/gallery/{FILE_NAME}",
        'folder_path'       => "public/storage/ep_events/images/{ID}/gallery/",
        'temp_folder_path'  => "temp/ep_events/{ENCRYPTED_FOLDER_NAME}/gallery/",
        'limit'             => 10,
        'aspect_ratio'      => 2,
        'resize'      => [
            'width' => 800,
            'height' => 'R'
        ],
        'rules' => [
            'min_width' => 800,
            'min_height' => 400,
            'format' => 'jpg,jpeg,png',
            'size' => 10 * 1024 * 1024,
            'size_placeholder' => '10MB',
            'exact_ratio'   => [
                'width'  => 2,
                'height' => 1,
            ],
        ],
        'thumbs' => [
            0 => [
                'name' => 'thumb_0_{THUMB_NAME}',
                'w' => 380,
                'h' => 'R',
            ]
        ]
    ],
    'recommended' => [
        'file_path'         => "public/storage/ep_events/images/{ID}/{FILE_NAME}",
        'folder_path'       => "public/storage/ep_events/images/{ID}/",
        'temp_folder_path'  => "temp/ep_events/{ENCRYPTED_FOLDER_NAME}/recommended/",
        'limit'             => 1,
        'aspect_ratio'      => 0.8,
        'resize'      => [
            'width' => 300,
            'height' => 'R'
        ],
        'rules' => [
            'min_width' => 300,
            'min_height' => 375,
            'format' => 'jpg,jpeg,png',
            'size' => 10 * 1024 * 1024,
            'size_placeholder' => '10MB',
            'exact_ratio'   => [
                'width'     => 4,
                'height'    => 5,
            ],
        ],
        'thumbs' => []
    ],
];
