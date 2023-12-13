<?php return [
    'main' => [
        'file_path'         => "public/storage/news/{FILE_NAME}",
        'temp_path'         => "public/temp/news/{USER_ID}/{ENCRYPTED_FOLDER_NAME}/{FILE_NAME}",
        'folder_path'       => "var/app/public/news/",
        'temp_folder_path'  => "var/temp/news/{USER_ID}/{ENCRYPTED_FOLDER_NAME}/",
        'limit'             => 1,
        'resize'      => [
            'height'    => 'R',
            'width'     => 325,
        ],
        'rules' => [
            'min_height'    => 245,
            'min_width'     => 325,
            'size'          => tmvc::instance()->my_config['fileupload_max_file_size'] ?? null,
        ],
    ],
];
