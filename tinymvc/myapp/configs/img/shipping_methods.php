<?php

return [
	'main' => [
		'file_path'   => 'public/storage/shipping_methods/{FILE_NAME}',
		'folder_path' => 'var/app/public/shipping_methods/',
		'resize'      => [
			'width'     => 280,
			'height'    => 'R',
		],
		'rules' => [
			'width'            => 300,
			'height'           => 300,
			'format'           => 'jpg,jpeg,png',
			'size'             => 10 * 1024 * 1024,
			'size_placeholder' => '10MB'
		],
		'thumbs' => []
	],
];
