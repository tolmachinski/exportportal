<?php return [
    'main' => [
        'file_path'         => "public/storage/vacancies/{FILE_NAME}",
        'temp_path'         => "public/temp/vacancies/{FILE_NAME}",
        'limit'             => 1,
        'resize'      => [
            'height'    => 200,
            'width'     => 'R',
        ],
        'rules' => [
            'min_height'    => 350,
            'min_width'     => 250,
            'size'          => tmvc::instance()->my_config['fileupload_max_file_size'] ?? null,
        ],
    ],
];
