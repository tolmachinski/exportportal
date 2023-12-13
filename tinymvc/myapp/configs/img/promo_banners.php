<?php return [
    'main' => [
        'file_path' => "public/storage/promo_banners/{ID}/{FILE_NAME}",
        'temp_path' => "public/temp/promo_banners/{ID}/{FOLDER}/{FILE_NAME}",
        'rules' => [
            'format' => 'jpg,jpeg',
            'size' => 10 * 1024 * 1024,
            'size_placeholder' => '10MB'
        ],
    ],
];
