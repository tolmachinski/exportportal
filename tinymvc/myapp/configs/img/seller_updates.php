<?php

return [
    'main' => [
        'file_path'        => 'public/storage/company/{ID}/updates/{FILE_NAME}',
        'folder_path'      => 'var/app/public/company/{COMPANY_ID}/updates/',
        'temp_folder_path' => 'temp/seller_library/{USER_ID}/{ENCRYPTED_FOLDER_NAME}/',
        'temp_file_path'   => "public/temp/{FILE_PATH}",
        'resize'           => [
            'width'     => 700,
            'height'    => 'R',
        ],
        'rules' => [
            'min_height'    => 60,
            'min_width'     => 60,
            'format'        => tmvc::instance()->my_config['fileupload_image_formats'] ?? 'jpg,jpeg,png,gif,bmp',
            'size'          => tmvc::instance()->my_config['fileupload_small_images_max_file_size'] ?? 10 * 1024 * 1024,
        ],
        'thumbs' => [
            1 => [
                'name'  => 'thumb_150xR_{THUMB_NAME}',
                'w'     => 150,
                'h'     => 'R',
            ],
        ],
    ],
];
