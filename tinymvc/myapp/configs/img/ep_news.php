<?php return [
    'main' => [
        'file_path'         => "public/storage/ep_news/{FILE_NAME}",
        'temp_path'         => "public/temp/ep_news/{USER_ID}/{ENCRYPTED_FOLDER_NAME}/{FILE_NAME}",
        'folder_path'       => "var/app/public/ep_news/",
        'temp_folder_path'  => "var/temp/ep_news/{USER_ID}/{ENCRYPTED_FOLDER_NAME}/",
        'limit'             => 1,
        'resize'      => [
            'height'    => 370,
            'width'     => 'R',
        ],
        'rules' => [
            'min_height'    => 140,
            'min_width'     => 235,
            'size'          => tmvc::instance()->my_config['fileupload_max_file_size'] ?? null,
        ],
        'thumbs' => [
            1 => [
                'name' => 'thumb_Rx195_{THUMB_NAME}',
                'w' => 'R',
                'h' => 195
            ],
        ]
    ],
];
