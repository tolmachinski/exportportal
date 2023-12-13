<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo __SITE_URL; ?>public/css/widget/style_new.css" />
</head>
<body>
<?php echo file_get_contents(sellerWidgetFilePath(id_session(), $key, $site)); ?>
</body>
</html>
