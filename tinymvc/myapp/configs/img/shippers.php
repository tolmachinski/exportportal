<?php return array(
    'main' => array(
        'file_path' => "public/storage/shippers/{ID}/logo/{FILE_NAME}",
        'folder_path' => "public/storage/shippers/{ID}/logo/",
        'resize'      => array(
            'width' => 280,
            'height' => 'R'
        ),
        'rules' => array(
            'min_width' => 280,
            'min_height' => 280,
            'format' => 'jpg,jpeg,png,gif,bmp',
            'size' => 10 * 1024 * 1024,
            'size_placeholder' => '10MB'
        ),
        'thumbs' => array(
            0 => array(
                'name' => 'thumb_0_{THUMB_NAME}',
                'w' => 80,
                'h' => 'R'
            ),
            1 => array(
                'name' => 'thumb_1_{THUMB_NAME}',
                'w' => 140,
                'h' => 'R'
            )
        )
    ),
    'photos' => array(
        'file_path' => "public/storage/shippers/{ID}/pictures/{FILE_NAME}",
        'folder_path' => "public/storage/shippers/{ID}/pictures/",
        'limit' => 15,
        'resize'      => array(
            'width' => 800,
            'height' => 'R'
        ),
        'rules' => array(
            'min_width' => 800,
            'min_height' => 600,
            'format' => 'jpg,jpeg,png,gif,bmp',
            'size' => 10 * 1024 * 1024,
            'size_placeholder' => '10MB',
            'ratio' => 2.5
        ),
        'thumbs' => array(
            2 => array(
                'name' => 'thumb_2_{THUMB_NAME}',
                'w' => 220,
                'h' => 135
            )
        )
    )
);
