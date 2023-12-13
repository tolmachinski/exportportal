<?php
    if (!$header_title) {
        $header_title = translate('about_us_nav_in_the_news_h1');
    }
    if (!$header_img) {
        $header_img = 'in_the_news_header2.jpg';
    }
?>
<div class="public-heading" id="js-public-heading">
    <div class="public-heading__container">
        <?php views()->display('new/about/partial_menu'); ?>

        <h1 class="public-heading__ttl"><?php echo $header_title;?></h1>
    </div>

    <img class="image" src="<?php echo __SITE_URL . 'public/img/headers-info-pages/' . $header_img;?>" alt="<?php echo translate('news_and_media_header_img_alt', null, true);?>">
</div>
