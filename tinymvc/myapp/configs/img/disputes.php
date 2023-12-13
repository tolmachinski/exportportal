<?php return [
    'main' => [
        'file_path'         => "public/storage/disputes/{ORDER_ID}/{FILE_NAME}",
        'temp_file_path'    => "public/temp/{FILE_PATH}",
        'folder_path'       => "var/app/public/disputes/{ID}",
        'temp_folder_path'  => "",
        'limit'             => 10,
        'resize'      => [
            'height'    => 1000,
            'width'     => 'R',
        ],
        'rules' => [
            'min_height'    => 250,
            'min_width'     => 250,
            'format'        => 'jpg,jpeg,png,gif,bmp',
            'size'          => tmvc::instance()->my_config['fileupload_max_file_size'] ?? null,
        ],
        'thumbs' => [
            0 => [
                'name' => 'thumb_Rx60_{THUMB_NAME}',
                'w' => 'R',
                'h' => 60
            ]
        ]
    ],
];
