<?php return [
    'cover' => [
        'temp_folder_path'  => "temp/downloadable_materials/{ENCRYPTED_FOLDER_NAME}/",
        'folder_path'       => "var/app/public/downloadable_materials/{ID}/",
        'file_path'         => "{FOLDER_PATH}{FILE_NAME}",
        'resize'            => [],
        'rules' => [
            'min_width' => 324,
            'max_width' => 324,
            'min_height' => 489,
            'max_height' => 489,
            'format' => 'jpg,jpeg,png',
        ],
        'thumbs' => [
            0 => [
                'name' => 'thumb_0_{THUMB_NAME}',
                'w' => 190,
                'h' => 'R'
            ]
        ]
    ],
];
