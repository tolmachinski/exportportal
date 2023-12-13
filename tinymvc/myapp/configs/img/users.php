<?php

return [
	'main' => [
		'file_path'   => 'public/storage/users/{ID}/{FILE_NAME}',
		'folder_path' => 'public/storage/users/{ID}/',
		'resize'      => [
			'width'     => 280,
			'height'    => 'R',
		],
        'watermark' => [
            'path'     => 'public/img/certified-badge.png',
            'position' => 'center',
            'prefix'   => 'badge_'
        ],
		'rules' => [
			'min_width'        => 280,
			'min_height'       => 280,
			'format'           => 'jpg,jpeg,png',
			'size'             => 10 * 1024 * 1024,
			'size_placeholder' => '10MB'
		],
		'thumbs' => [
			0 => [
				'name'      => 'thumb_0_{THUMB_NAME}',
				'w'         => 80,
				'h'         => 'R',
				'watermark' => true,
			],
			1 => [
				'name'      => 'thumb_1_{THUMB_NAME}',
				'w'         => 155,
				'h'         => 'R',
				'watermark' => true,
			]
		]
	],
	'photos' => [
		'file_path'   => 'public/storage/users/{ID}/{FILE_NAME}',
		'folder_path' => 'public/storage/users/{ID}/',
		'limit'       => 10,
		'resize'      => [
			'width'  => 800,
			'height' => 'R'
		],
		'rules' => [
			'min_width'        => 800,
			'min_height'       => 600,
			'format'           => 'jpg,jpeg,png',
			'size'             => 10 * 1024 * 1024,
			'size_placeholder' => '10MB',
			'ratio'            => 2.5
		],
		'thumbs' => [
			2 => [
				'name' => 'thumb_2_{THUMB_NAME}',
				'w'    => 220,
				'h'    => 135,
				'fit'  => 'cover'//'cover', 'contain'
			]
		]
	]
];
