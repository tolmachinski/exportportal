<?php

return [
	'main' => [
		'file_path'   => 'public/storage/dashboard_banners/{FILE_NAME}',
		'folder_path' => 'var/app/public/dashboard_banners/',
		'resize'      => [
			'width'     => 280,
			'height'    => 'R',
		],
		'rules' => [
			'width'        => 140,
			'height'       => 65,
			'format'           => 'jpg,jpeg,png',
			'size'             => 10 * 1024 * 1024,
			'size_placeholder' => '10MB'
		],
		'thumbs' => []
	],
];
