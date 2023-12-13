<link href="<?php echo __FILES_URL;?>public/css/style.css" rel="stylesheet" />

<?php
	$replace = array('{base_url}');
	$replace_with = array(__SITE_URL);
	$body = file_get_contents('tinymvc/myapp/views/admin/epicons/iconsEP.html');

	$content = str_replace($replace,$replace_with,$body);
	echo $content;
?>

