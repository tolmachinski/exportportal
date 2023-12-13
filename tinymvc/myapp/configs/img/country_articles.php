<?php return [
    'main' => [
        'file_path'         => "public/storage/country_articles/{ARTICLE_ID}/{FILE_NAME}",
        'temp_file_path'    => "public/temp/{FILE_PATH}",
        'folder_path'       => "var/app/public/scountry_articles/{ID}",
        'temp_folder_path'  => "temp/country_articles/{USER_ID}/{ENCRYPTED_FOLDER_NAME}/",
        'limit'             => 1,
        'resize'      => [
            'height'    => 200,
            'width'     => 'R',
        ],
        'rules' => [
            'min_height'    => 200,
            'min_width'     => 200,
            'size'          => tmvc::instance()->my_config['fileupload_max_file_size'] ?? null,
        ],
    ],
];
