<?php

return [
    'main' => [
        'file_path'        => 'public/storage/b2b/{FOLDER_PATH}/{FILE_NAME}',
        'folder_path'      => 'var/app/public/b2b/{ID}/',
        'temp_folder_path' => 'temp/b2b/{ID}/{FOLDER}/main/',
        'rules' => [
            'min_width'        => 640,
            'min_height'       => 480,
            'format'           => 'jpg,jpeg,png',
            'size'             => 10 * 1024 * 1024,
            'size_placeholder' => '10MB',
        ],
        'watermark'             => [
            'width'             => 0.1,
			'apply_on_original' => true,
			'path'              => 'public/img/watermark.png',
			'position'          => 'bottom-right',
			'prefix'            => '',
		],
        'thumbs' => [
            1 => [
                'name'      => 'thumb_1_{THUMB_NAME}',
                'w'         => 160,
                'h'         => 'R',
				'watermark' => true,
            ],
            2 => [
                'name'      => 'thumb_2_{THUMB_NAME}',
                'w'         => 320,
                'h'         => 'R',
				'watermark' => true,
            ],
            3 => [
                'name'      => 'thumb_3_{THUMB_NAME}',
                'w'         => 450,
                'h'         => 'R',
				'watermark' => true,
            ],
        ],
    ],
    'photos' => [
        'file_path'        => 'public/storage/b2b/{FOLDER_PATH}/images/{FILE_NAME}',
        'folder_path'      => 'public/img/b2b/{ID}/images/',
        'temp_folder_path' => 'temp/b2b/{ID}/{FOLDER}/pictures/',
        'limit'            => 10,
        'resize'           => [
            'width'  => 850,
            'height' => 'R',
        ],
        'rules' => [
            'min_width'        => 850,
            'min_height'       => 640,
            'format'           => 'jpg,jpeg,png',
            'size'             => 10 * 1024 * 1024,
            'size_placeholder' => '10MB',
        ],
        'watermark'             => [
            'width'             => 0.1,
			'apply_on_original' => true,
			'path'              => 'public/img/watermark.png',
			'position'          => 'bottom-right',
			'prefix'            => '',
		],
        'thumbs' => [
            1 => [
                'name'      => 'thumb_1_{THUMB_NAME}',
                'w'         => 222,
                'h'         => 'R',
				'watermark' => true,
            ],
        ],
    ],
];
