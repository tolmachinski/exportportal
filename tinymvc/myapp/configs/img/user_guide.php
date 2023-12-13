<?php return [
    'inline' => [
        'file_path'         => "{FOLDER_PATH}{FILE_NAME}",
        'folder_path'       => "public/img/documentation/{ID}/",
        'temp_folder_path'  => "temp/user_guide/inline/{ENCRYPTED_FOLDER_NAME}/",
        'limit'             => 10,
        'rules' => [
            'format' => 'jpg,jpeg,png',
        ],
    ],
];
