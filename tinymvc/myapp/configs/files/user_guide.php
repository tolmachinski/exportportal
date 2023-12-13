<?php return [
    'pdf' => [
        'folder_path' => "public/user_guide/{GUIDE_NAME}/{LANG}/",
        'file_path'   => "{FOLDER_PATH}{FILE_NAME}",
        'resize'      => [],
        'rules'       => [
            'size_placeholder'  => '20 MB',
            'format'            => 'pdf',
            'size'              => 20 * 1048576, // 20M
        ],
    ],
];
