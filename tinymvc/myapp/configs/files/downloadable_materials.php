<?php return [
    'pdf' => [
        'temp_folder_path'  => "temp/downloadable_materials/pdf/{ENCRYPTED_FOLDER_NAME}/",
        'folder_path'       => "var/app/public/downloadable_materials/{ID}/",
        'file_path'         => "{FOLDER_PATH}{FILE_NAME}",
        'resize'            => [],
        'rules' => [
            'size_placeholder'  => '20 MB',
            'format'            => 'pdf',
            'size'              => 20 * 1048576, // 20M
        ],
    ],
];
