<div class="mobile-links">
    <a class="btn btn-primary btn-panel-left fancyboxSidebar fancybox" data-title="Menu" href="#about-flex-card__fixed-left">
        <i class="ep-icon ep-icon_items"></i> <?php echo translate('about_us_nav_menu_btn');?>
    </a>
</div>

<div class="partnership">
    <div class="partnership__other">
        <div class="other__titles">
            <h2><?php echo translate('about_us_other_partners_header'); ?></h2>
            <p><?php echo translate('about_us_other_partners_block_1'); ?></p>
        </div>

        <div class="partnership__blocks">
            <?php foreach ($otherPartners as $partner) {?>
                <div class="block__partner">
                    <div class="block__partner--left">
                        <img src="<?php  echo $partner['image']; ?>" alt="<?php echo $partner['partner_name']; ?>">
                    </div>
                    <div class="block__partner--right">
                        <p class="block__partner-title"><?php echo $partner['partner_name']; ?></p>
                        <p class="block__partner-paragraph">
                            <?php echo $partner['description_partner']; ?>
                        </p>
                        <a class="block__partner-link" href="<?php echo $partner['website_partner'] ?>" target="_blank"><?php echo translate('about_us_other_partners_link_to_site_btn');?><span>&gt;</span></a>
                    </div>
                </div>
            <?php }?>
        </div>

        <div class="partners__buttons">
            <a href="<?php echo __SITE_URL . 'about/link_to_us';?>" class="btn btn-primary w-230"><?php echo translate('about_us_nav_link_to_us'); ?></a>
        </div>
    </div>
</div>
