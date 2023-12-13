<?php

return [
	'main' => [
		'file_path'             => 'public/storage/items/{ID}/{FILE_NAME}',
		'folder_path'           => 'public/storage/items/{ID}/',
		'temp_folder_path'      => '',
        'relative_disk_path'    => 'items/{ID}/',
		'resize'           => [
			'width'  => 640,
			'height' => 'R'
		],
		'watermark' => [
			'apply_on_original' => true,
			'path'              => 'public/img/watermark.png',
			'position'          => 'bottom-right',
			'prefix'            => '',
		],
		'rules' => [
			'min_width'        => 640,
			'min_height'       => 480,
			'format'           => 'jpg,jpeg,png',
			'size'             => 10 * 1024 * 1024,
			'size_placeholder' => '10MB',
			'ratio'            => 2.5
		],
		'thumbs' => [
			1 => [
				'name'      => 'thumb_1_{THUMB_NAME}',
				'w'         => 150,
				'h'         => 'R',
				'watermark' => true,
			],
			2 => [
				'name'      => 'thumb_2_{THUMB_NAME}',
				'w'         => 225,
				'h'         => 'R',
				'watermark' => true,
			],
			3 => [
				'name'      => 'thumb_3_{THUMB_NAME}',
				'w'         => 375,
				'h'         => 'R',
				'watermark' => true,
			],
			4 => [
				'name' => 'orig_{THUMB_NAME}',
				'w'    => 640,
				'h'    => 'R',
			]
		]
	],
	'photos' => [
		'file_path'             => 'public/storage/items/{ID}/{FILE_NAME}',
		'folder_path'           => 'public/storage/items/{ID}/',
		'temp_folder_path'      => '',
        'relative_disk_path'    => 'items/{ID}/',
		'limit'            => 10,
		'resize'           => [
			'width'  => 850,
			'height' => 'R'
		],
		'watermark' => [
			'apply_on_original' => true,
			'path'              => 'public/img/watermark.png',
			'position'          => 'bottom-right',
			'prefix'            => '',
		],
		'rules' => [
			'min_width'        => 850,
			'min_height'       => 640,
			'format'           => 'jpg,jpeg,png',
			'size'             => 10 * 1024 * 1024,
			'size_placeholder' => '10MB',
			'ratio'            => 2.5
		],
		'thumbs' => [
			1 => [
				'name'      => 'thumb_1_{THUMB_NAME}',
				'w'         => 'R',
				'h'         => 125,
				'watermark' => true,
			],
			4 => [
				'name'      => 'orig_{THUMB_NAME}',
				'w'         => 2000,
				'h'         => 'R',
			]
		]
	],
	'snapshot' => [
		'file_path'   => 'public/storage/snapshots/{ID}/{FILE_NAME}',
		'folder_path' => 'public/storage/snapshots/{ID}/',
		'thumbs'      => [
			1 => [
				'name' => 'thumb_1_{THUMB_NAME}',
				'w'    => 150,
				'h'    => 'R'
			],
			2 => [
				'name' => 'thumb_2_{THUMB_NAME}',
				'w'    => 225,
				'h'    => 'R'
			],
			3 => [
				'name' => 'thumb_3_{THUMB_NAME}',
				'w'    => 375,
				'h'    => 'R'
			]
		]
	],
	'prototype' => [
		'file_path'   => 'public/storage/prototype/{ID}/{FILE_NAME}',
		'folder_path' => 'public/storage/prototype/{ID}/',
		'resize'      => [
			'width'  => 640,
			'height' => 'R'
		],
		'rules' => [
			'min_width'        => 640,
			'min_height'       => 512,
			'format'           => 'jpg,jpeg,png',
			'size'             => 10 * 1024 * 1024,
			'size_placeholder' => '10MB',
			'ratio'            => 2.5
		],
		'thumbs' => [
			1 => [
				'name' => 'thumb_1_{THUMB_NAME}',
				'w'    => 150,
				'h'    => 'R'
			],
			2 => [
				'name' => 'thumb_2_{THUMB_NAME}',
				'w'    => 225,
				'h'    => 'R'
			],
			3 => [
				'name' => 'thumb_3_{THUMB_NAME}',
				'w'    => 375,
				'h'    => 'R'
			]
		]
	],
	'wall' => [
		'file_path'   => '{FOLDER_PATH}{FILE_NAME}',
		'folder_path' => 'public/wall/{ID_SELLER}/{ID}/',
		'thumbs'      => [
			1 => [
				'name' => 'thumb_1_{THUMB_NAME}',
				'w'    => 150,
				'h'    => 'R'
			],
			2 => [
				'name' => 'thumb_2_{THUMB_NAME}',
				'w'    => 225,
				'h'    => 'R'
			],
			3 => [
				'name' => 'thumb_3_{THUMB_NAME}',
				'w'    => 375,
				'h'    => 'R'
			]
		]
	],
];
