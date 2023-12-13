<?php return [
    'main' => [
        //'temp_folder_path'  => "temp/downloadable_materials/pdf/{ENCRYPTED_FOLDER_NAME}/",
        'folder_path'       => "var/app/public/bulk_upload/",
        'file_path'         => "public/storage/bulk_upload/{FILE_NAME}",
        'resize'            => [],
        'rules' => [
            'size'   => 10 * 1024 * 1024,
            'format' => 'xls,xlsx',
        ],
    ],
];
