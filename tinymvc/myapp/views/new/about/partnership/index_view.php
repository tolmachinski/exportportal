<div class="mobile-links">
    <a class="btn btn-primary btn-panel-left fancyboxSidebar fancybox" data-title="Menu" href="#about-flex-card__fixed-left">
        <i class="ep-icon ep-icon_items"></i> <?php echo translate('about_us_nav_menu_btn');?>
    </a>
</div>

<div class="partnership partnership-new">
    <h2 class="partnership__pre-container__title"><?php echo translate('about_us_members_protect_block_3_header'); ?></h2>

    <div class="partnership__container">
        <div class="partnership__left-text">
            <p><?php echo translate('about_us_members_protect_block_1'); ?></p>
            <p><?php echo translate('about_us_members_protect_block_3_1'); ?></p>
        </div>
        <div class="partnership__right-image">
            <img class="image" src="<?php echo __IMG_URL . 'public/img/about/partnership/exima.jpg';?>" alt="EXIMA Partner">
        </div>
    </div>

    <?php if(config('env.APP_ENV') === 'dev'){?>
        <div class="partnership-titles_new partnership-titles_new__head">
            <h2><?php echo translate('about_us_other_partners_header'); ?></h2>
        </div>

        <div class="partners__block">
            <?php foreach ($otherPartners as $partner) { ?>
                <a href="<?php echo $partner['website_partner']; ?>" target="_blank">
                    <div class="partners__block-partner">
                        <img class="partners__block-partner--logo" src="<?php echo $partner['image']; ?>" <?php echo addQaUniqueIdentifier("about-partnership__partner-image");?>>
                    </div>
                </a>
            <?php } ?>
        </div>

        <div class="partners__buttons">
            <a href="<?php echo __SITE_URL . 'about/link_to_us';?>" class="btn btn-primary w-230"><?php echo translate('about_us_nav_link_to_us'); ?></a>
            <a href="<?php echo __SITE_URL . 'about/other_partners';?>" class="btn btn-light w-230"><?php echo translate('about_us_view_more'); ?></a>
        </div>
    <?php }?>

    <div class="partnership-titles_new partnership-titles_new__foot">
        <h2><?php echo translate('about_us_members_protect_block_2_header'); ?></h2>
        <!-- <p><?php //echo translate('about_us_members_protect_block_2'); ?></p> -->
    </div>

</div>
