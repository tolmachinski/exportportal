<?php return [
    'desktop' => [
        'temp_folder_path'  => 'temp/items_compilation/{ENCRYPTED_FOLDER_NAME}/desktop/',
        'folder_path'       => 'var/app/public/items_compilation/',
        'file_path'         => 'public/storage/items_compilation/{FILE_NAME}',
        'limit'             => 1,
        'rules' => [
            'format'    => 'jpg,jpeg,png',
            'width'     => 453,
            'height'    => 299,
        ],
    ],
    'tablet' => [
        'temp_folder_path'  => 'temp/items_compilation/{ENCRYPTED_FOLDER_NAME}/tablet/',
        'folder_path'       => 'var/app/public/items_compilation/',
        'file_path'         => 'public/storage/items_compilation/{FILE_NAME}',
        'limit'             => 1,
        'rules'             => [
            'format'        => 'jpg,jpeg,png',
            'width'         => 700,
            'height'        => 299,
        ],
    ],
];
