<div class="info-block footer-connect">

    <div class="info-block__info">
        <div class="info-block__title"><?php echo translate('about_us_in_the_news_who_we_are_block_title');?></div>
        <p class="info-block__text">
            <?php echo translate('about_us_in_the_news_who_we_are_block_subtitle');?>
        </p>
        <a class="btn btn-outline-dark"
            href="<?php echo __SITE_URL . 'about';?>"
            <?php echo addQaUniqueIdentifier('global__who-we-are__learn-more-btn') ?>>
            <?php echo translate('about_us_in_the_news_who_we_are_block_learn_more_btn');?>
        </a>
    </div>

    <picture class="info-block__image">
        <source media="(max-width:991px)" srcset="<?php echo getLazyImage(1920, 1608); ?>" data-srcset="<?php echo __IMG_URL . 'public/img/footers-info-pages/group-of-people2-tablet.jpg';?>">
        <img class="js-lazy image" src="<?php echo getLazyImage(1920, 1608); ?>" data-src="<?php echo __IMG_URL . 'public/img/footers-info-pages/group-of-people2.jpg';?>" alt="Import Export Trade">
    </picture>

</div>
