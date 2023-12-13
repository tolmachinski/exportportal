<?php return array( 
    'main' => array(
        'file_path' => "{FOLDER_PATH}{FILE_NAME}",
        'folder_path' => "public/items_draft/{ID}/{FOLDER}/",
        'temp_folder_path' => "temp/items_draft/{ID}/{FOLDER}/",
        'resize'      => array(
            'width' => 640,
            'height' => 'R'
        ),
        'rules' => array(
            'min_width' => 640,
            'min_height' => 512,
            'format' => 'jpg,jpeg,png',
            'size' => 10 * 1024 * 1024,
            'size_placeholder' => '10MB',
            'ratio' => 2.5
        ),
    ),
    'photos' => array(
        'file_path' => "{FOLDER_PATH}{FILE_NAME}",
        'folder_path' => "public/items_draft/{ID}/{FOLDER}/",
        'temp_folder_path' => "temp/items_draft/{ID}/{FOLDER}/",
        'limit' => 10,
        'resize'      => array(
            'width' => 850,
            'height' => 'R'
        ),
        'rules' => array(
            'min_width' => 850,
            'min_height' => 680,
            'format' => 'jpg,jpeg,png',
            'size' => 10 * 1024 * 1024,
            'size_placeholder' => '10MB',
            'ratio' => 2.5
        ),
        'thumbs' => array(
            1 => array(
                'name' => 'thumb_1_{THUMB_NAME}',
                'w' => 'R',
                'h' => 125
            ),
        )
    )
);