<?php return [
    'main' => [
        'file_path'         => "public/storage/category_articles/{ARTICLE_ID}/{FILE_NAME}",
        'temp_file_path'    => "public/temp/{FILE_PATH}",
        'folder_path'       => "var/app/public/category_articles/{ID}",
        'temp_folder_path'  => "temp/categories_articles/{USER_ID}/{ENCRYPTED_FOLDER_NAME}/",
        'limit'             => 1,
        'resize'      => [
            'height'    => 200,
            'width'     => 'R',
        ],
        'rules' => [
            'min_height'    => 200,
            'min_width'     => 235,
            'format'        => 'jpg,jpeg,png,gif,bmp',
            'size'          => tmvc::instance()->my_config['fileupload_max_file_size'] ?? null,
        ],
    ],
];
