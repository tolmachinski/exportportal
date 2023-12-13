<?php

return [
    'main' => [
        'file_path'        => 'public/storage/company/{ID}/news/{FILE_NAME}',
        'folder_path'      => 'var/app/public/company/{COMPANY_ID}/news/',
        'temp_folder_path' => 'temp/seller_news/{USER_ID}/{ENCRYPTED_FOLDER_NAME}/',
        'temp_file_path'   => 'public/temp/{FILE_PATH}',
        'resize'           => [
            'width'     => 730,
            'height'    => 'R',
        ],
        'rules' => [
            'min_width'  => tmvc::instance()->my_config['news_picture_min_width'] ?? 150,
            'min_height' => tmvc::instance()->my_config['news_picture_min_height'] ?? 150,
            'format'     => tmvc::instance()->my_config['fileupload_image_formats'] ?? 'jpg,jpeg,png,gif,bmp',
            'size'       => tmvc::instance()->my_config['fileupload_max_file_size'] ?? 10 * 1024 * 1024,
        ],
        'thumbs' => [
            1 => [
                'name' => 'thumb_110xR_{THUMB_NAME}',
                'w'    => 110,
                'h'    => 'R',
            ],
        ],
    ],
];
