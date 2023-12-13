<title><?php echo $meta['title']?></title>
<meta itemprop='name' content="ExportPortal" />
<link rel="canonical" href="<?php echo $this->seoPageService->getCanonicalUrl(); ?>">

<meta name="description" content="<?php echo $meta['description']?>" />
<meta name="keywords" content="<?php echo $meta['keywords']?>" />

<meta property="og:type" content="website" />
<meta property="og:site_name" content="ExportPortal">
<meta property="og:title" content="<?php echo $meta['title']?>" />
<meta property="og:description" content="<?php echo $meta['description']?>" />
<meta property="og:url" content="<?php echo __CURRENT_URL;?>" />
<?php $og_image_name = $meta['image']; ?>
<?php $og_image_link = 'public/img/og-images/'; ?>
<?php if(image_exist($og_image_link . '180x110_'.$og_image_name)){ ?>
<meta property="og:image" content="<?php echo __IMG_URL.$og_image_link;?>180x110_<?php echo $og_image_name;?>" />
<meta property="og:image:width" content="180">
<meta property="og:image:height" content="110">
<meta property="og:image" content="<?php echo __IMG_URL.$og_image_link;?>400x400_<?php echo $og_image_name;?>" />
<meta property="og:image:width" content="400">
<meta property="og:image:height" content="400">
<meta property="og:image" content="<?php echo __IMG_URL.$og_image_link;?>600x315_<?php echo $og_image_name;?>" />
<meta property="og:image:width" content="600">
<meta property="og:image:height" content="315">
<?php }elseif(!empty($meta['image']) && image_exist($meta['image'])){ ?>
<?php list($image_width, $image_height, $image_type, $image_attr) = getimagesize($meta['image']); ?>
<meta property="og:image" content="<?php echo __IMG_URL.$meta['image'];?>" />
<meta property="og:image:width" content="<?php echo $image_width;?>">
<meta property="og:image:height" content="<?php echo $image_height;?>">
<?php }else{ ?>
<meta property="og:image" content="<?php echo __IMG_URL;?>public/img/og-images/img-og-r.jpg" />
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="628">
<?php }?>

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo $meta['title']?>">
<meta name="twitter:description" content="<?php echo $meta['description']?>">
<meta name="twitter:url" content="<?php echo __CURRENT_URL;?>">
<meta name="twitter:domain" content="<?php echo __SITE_URL;?>">
<meta name="twitter:site" content="@ExportPortal">
<?php if(image_exist($og_image_link . '180x110_'.$og_image_name)){ ?>
<meta name="twitter:image" content="<?php echo __IMG_URL.$og_image_link;?>600x315_<?php echo $og_image_name;?>">
<?php }elseif(!empty($meta['image']) && image_exist($meta['image'])){ ?>
<meta property="twitter:image" content="<?php echo __SITE_URL.$meta['image'];?>" />
<?php }else{ ?>
<meta name="twitter:image" content="<?php echo __IMG_URL;?>public/img/og-images/img-og-r.jpg">
<?php }?>

<?php tmvc::instance()->controller->view->display('new/epl/template/favicon_view'); ?>
