<?php return [
    'main' => [
        'file_path'                 => 'public/storage/product_reviews/{REVIEW_ID}/{FILE_NAME}',
        'folder_path'               => 'var/app/public/product_reviews/{REVIEW_ID}/',
        'temp_file_path'            => 'public/temp/product_reviews/{ENCRYPTED_FOLDER_NAME}/{FILE_NAME}',
        'temp_folder_path'          => 'var/temp/product_reviews/{ENCRYPTED_FOLDER_NAME}/',
        'relative_temp_folder_path' => 'product_reviews/{ENCRYPTED_FOLDER_NAME}/',
        'limit'                     => 10,
        'watermark'                 => [
            'width'             => 0.2,
			'apply_on_original' => true,
			'path'              => 'public/img/watermark.png',
			'position'          => 'bottom-right',
			'prefix'            => '',
		],
        'rules'                     => [
            'size_placeholder'  => '10MB',
            'min_height'        => 680,
            'min_width'         => 850,
            'format'            => 'jpg,jpeg,png',
            'size'              => 10 * 1024 * 1024,
            'ratio'             => 2.5,
        ],
        'thumbs'                    => [
            0 => [
                'name' => 'thumb_0_{THUMB_NAME}',
                'w' => 270,
                'h' => 'R',
                'watermark' => true,
            ],
        ]
    ],
];
