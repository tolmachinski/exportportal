<?php return array(
    'main' => array(
        'file_path' => "public/storage/trade_news/{ID}/{FILE_NAME}",
        'folder_path' => "public/storage/trade_news/{ID}/",
		'aspect_ratio' => 3.8,
        'resize'      => array(
            'width' => 1197,
            'height' => 'R'
        ),
        'rules' => array(
            'min_width' => 1197,
            'min_height' => 315,
            'format' => 'jpg,jpeg',
            'size' => 10 * 1024 * 1024,
            'size_placeholder' => '10MB'
        ),
        'thumbs' => array(
			5 => array(
                'name' => 'thumb_5_{THUMB_NAME}',
                'w' => 580,
                'h' => 'R'
            ),
            8 => array(
                'name' => 'thumb_8_{THUMB_NAME}',
                'w' => 855,
                'h' => 'R'
            )
        )
    ),
    'photos' => array(
        'file_path' => "public/storage/trade_news/{ID}/text_photos/{FILE_NAME}",
        'folder_path' => "public/storage/trade_news/{ID}/text_photos/",
        'limit' => 10,
        'resize'      => array(
            'width' => 1200,
            'height' => 'R'
        ),
        'rules' => array(
            'min_width' => 1200,
            'min_height' => 315,
            'format' => 'jpg,jpeg',
            'size' => 10 * 1024 * 1024,
            'size_placeholder' => '10MB'
        )
    )
);
