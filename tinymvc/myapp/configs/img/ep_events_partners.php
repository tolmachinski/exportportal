<?php

return [
    'main' => [
        'file_path'        => 'public/storage/ep_events/partners/{ID}/{FILE_NAME}',
        'folder_path'      => 'public/storage/ep_events/partners/{ID}/',
        'temp_folder_path' => 'temp/ep_events/partners/{ENCRYPTED_FOLDER_NAME}',
        'resize'      => [
            'width' => 280,
            'height' => 'R'
        ],
        'rules'            => [
            'min_width'        => 280,
            'min_height'       => 200,
            'format'           => 'jpg,jpeg,png',
            'size'             => 10 * 1024 * 1024,
            'size_placeholder' => '10MB',
        ],
        // 'thumbs' => [
        //     0 => [
        //         'name' => 'thumb_0_{THUMB_NAME}',
        //         'w' => 180,
        //         'h' => 'R'
        //     ],
        // ]
    ],
];
