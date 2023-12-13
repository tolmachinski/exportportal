<?php return [
    'logo' => [
        'file_path'         => "public/storage/media/{FILE_NAME}",
        'temp_path'         => "public/temp/media/{USER_ID}/{ENCRYPTED_FOLDER_NAME}/{FILE_NAME}",
        'folder_path'       => "var/app/public/media/",
        'temp_folder_path'  => "var/temp/media/{USER_ID}/{ENCRYPTED_FOLDER_NAME}/",
        'limit'             => 1,
        'resize'      => [
            'height'    => 20,
            'width'     => 'R',
        ],
        'rules' => [
            'min_height'    => 90,
            'min_width'     => 155,
            'size'          => tmvc::instance()->my_config['fileupload_max_file_size'] ?? null,
        ],
    ],
];
