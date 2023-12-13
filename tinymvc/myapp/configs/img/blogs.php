<?php return [
    'main' => [
        'file_path'         => "public/storage/blogs/{FILE_NAME}",
        'temp_path'         => "public/temp/blogs/{FILE_NAME}",
        'limit'             => 1,
        'resize'      => [
            'height'    => 'R',
            'width'     => 980,
        ],
        'rules' => [
            'size'       => tmvc::instance()->my_config['fileupload_max_file_size'] ?? null,
            'min_width'  => tmvc::instance()->my_config['blogs_photos_main_min_width'] ?? null,
            'min_height' => tmvc::instance()->my_config['blogs_photos_main_min_height'] ?? null,
            'format'     => tmvc::instance()->my_config['blogs_photos_main_accept'] ?? null,
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
            ],
            2 => [
                'name' => 'thumb_660xR_{THUMB_NAME}',
                'w' => 660,
                'h' => 'R'
            ],
        ],
    ],
    'inline' => [
        'file_path'         => "public/storage/blogs/{ID}/text_photos/{FILE_NAME}",
        'file_path'         => "public/temp/blogs/{USER_ID}/{ENCRYPTED_FOLDER_NAME}/text_photos/{FILE_NAME}",
        'limit'             => 1,
        'resize'      => [
            'height'    => 'R',
            'width'     => 980,
        ],
        'rules' => [
            'size'       => tmvc::instance()->my_config['fileupload_max_file_size'] ?? null,
            'min_width'  => tmvc::instance()->my_config['blogs_photos_in_text_min_width'] ?? null,
            'min_height' => tmvc::instance()->my_config['blogs_photos_in_text_min_height'] ?? null,
            'format'     => tmvc::instance()->my_config['blogs_photos_in_text_accept'] ?? null,
        ],
    ],
];
