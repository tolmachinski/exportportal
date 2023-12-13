<?php return [
    'main' => [
        'file_path'         => "public/storage/bloggers/uploads/{ID}/main/{FILE_NAME}",
        'temp_path'         => "public/temp/bloggers/{ENCRYPTED_FOLDER_NAME}/main/{FILE_NAME}",
        'limit'             => 1,
        'resize'      => [
            'height'    => 'R',
            'width'     => 1150,
        ],
        'rules' => [
            'min_height'       => 500,
            'min_width'        => 1150,
            'format'           => 'jpg,jpeg,png,bmp',
            'size'             => 10 * 1024 * 1024,
        ],
        'thumbs' => [
            0 => [
                'name' => 'thumb_200xR_{THUMB_NAME}',
                'w' => 200,
                'h' => 'R'
            ],
            1 => [
                'name' => 'thumb_364xR_{THUMB_NAME}',
                'w' => 364,
                'h' => 'R'
            ]
        ]
    ],
    'inline' => [
        'file_path'         => "public/storage/bloggers/uploads/{ID}/text_photos/{FILE_NAME}",
        'temp_path'         => "public/temp/bloggers/{ENCRYPTED_FOLDER_NAME}/text_photos/{FILE_NAME}",
        'resize'      => [
            'height'    => 'R',
            'width'     => 1150,
        ],
        'rules' => [
            'min_height'    => 500,
            'min_width'     => 1150,
            'format'        => 'jpg,jpeg,png,bmp',
            'size'          => 2 * 1024 * 1024,
        ],
    ],
];
