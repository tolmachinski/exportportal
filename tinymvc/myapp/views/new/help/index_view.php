<script>
    var scrollToContact = function(obj){
        scrollToElement('#contact-form');
    }
</script>

<div class="container-center-sm">
    <div class="title-public pt-30">
        <h1 class="title-public__txt"><?php echo translate('help_h1');?></h1>
    </div>

    <div class="help-row">
        <div class="hover-card hover-card--highlight active">
            <div class="hover-card__image">
                <img src="<?php echo __IMG_URL . 'public/img/help/faq-thumb.jpg';?>" alt="faq" />
                <div class="hover-card__title-block">
                    <span class="hover-card__title"><?php echo translate('help_nav_header_faq');?></span>
                    <p class="hover-card__text"><?php echo translate('help_nav_text_faq');?></p>
                    <a href="<?php echo __SITE_URL . 'faq';?>" class="hover-card__link">
                        <?php echo translate('help_nav_link_try_now');?> <i class="ep-icon ep-icon_arrow-right fs-9"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="hover-card" <?php echo addQaUniqueIdentifier("help__hover-card")?>>
            <div class="hover-card__image">
                <img src="<?php echo __IMG_URL . 'public/img/help/topics-thumb.jpg';?>" alt="topics" />
                <div class="hover-card__title-block">
                    <span class="hover-card__title"><?php echo translate('help_nav_header_topics');?></span>
                    <p class="hover-card__text"><?php echo translate('help_nav_text_topics');?></p>
                    <a href="<?php echo __SITE_URL . 'topics';?>" class="hover-card__link">
                        <?php echo translate('help_nav_link_try_now');?> <i class="ep-icon ep-icon_arrow-right fs-9"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="hover-card">
            <div class="hover-card__image">
                <img src="<?php echo __IMG_URL . 'public/img/help/user-guides-thumb.jpg';?>" alt="<?php echo translate('help_user_guides', null, true);?>" />
                <div class="hover-card__title-block">
                    <span class="hover-card__title"><?php echo translate('help_user_guides');?></span>
                    <p class="hover-card__text"><?php echo translate('help_nav_text_user_guides');?></p>
                    <a href="<?php echo __SITE_URL . 'user_guide';?>" class="hover-card__link">
                        <?php echo translate('help_nav_link_try_now');?> <i class="ep-icon ep-icon_arrow-right fs-9"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="hover-card">
            <div class="hover-card__image">
                <img src="<?php echo __IMG_URL . 'public/img/help/community-thumb.jpg';?>" alt="community" />
                <div class="hover-card__title-block">
                    <span class="hover-card__title"><?php echo translate('help_nav_header_comunity_help');?></span>
                    <p class="hover-card__text"><?php echo translate('help_nav_text_comunity_help');?></p>
                    <a href="<?php echo __COMMUNITY_URL?>" class="hover-card__link"><?php echo translate('help_nav_link_comunity_help');?> <i class="ep-icon ep-icon_arrow-right fs-9"></i></a>
                </div>
            </div>
        </div>
        <div class="hover-card">
            <div class="hover-card__image">
                <img src="<?php echo __IMG_URL . 'public/img/help/contact-thumb.jpg';?>" alt="contact" />
                <div class="hover-card__title-block">
                    <span class="hover-card__title"><?php echo translate('help_contact_us');?></span>
                    <p class="hover-card__text"><?php echo translate('help_nav_text_contact_us');?></p>
                    <a href="<?php echo __SITE_URL . 'contact';?>" class="hover-card__link">
                        <?php echo translate('help_nav_link_try_now');?> <i class="ep-icon ep-icon_arrow-right fs-9"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="hover-card">
            <div class="hover-card__image">
                <img src="<?php echo __IMG_URL . 'public/img/help/chat-thumb.jpg';?>" alt="chat" />
                <div class="hover-card__title-block">
                    <span class="hover-card__title"><?php echo translate('help_nav_header_live_chat_now');?></span>
                    <p class="hover-card__text"><?php echo translate('help_nav_text_live_chat_now');?></p>
                    <a href="#" class="hover-card__link call-action" data-js-action="zoho-chat:show" title="<?php echo translate('help_nav_header_live_chat_now_title');?>">
                        <?php echo translate('help_nav_link_try_now');?> <i class="ep-icon ep-icon_arrow-right fs-9"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-center-sm" <?php echo addQaUniqueIdentifier('help__banner-demo'); ?>>
    <?php echo widgetShowBanner('help_before_who_we_are', 'promo-banner-wr--help'); ?>
</div>

<?php if(!isset($webpackData)) { ?>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/js/webinar_requests/schedule-demo-popup.js');?>"></script>
<?php } ?>

<?php app()->view->display('new/about/bottom_who_we_are_view'); ?>
