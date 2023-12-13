<div class="header-learnmore-container">
    <picture class="learnmore-header__background">
        <source media="(max-width: 425px)" srcset="<?php echo asset('public/build/images/learn-more/learn-more_header-mobile.jpg'); ?>">
        <source media="(min-width: 426px) and (max-width: 991px)" srcset="<?php echo asset('public/build/images/learn-more/learn-more_header-tablet.jpg'); ?>">
        <img class="image" src="<?php echo asset('public/build/images/learn-more/learn-more_header.jpg'); ?>" alt="<?php echo translate('learn_more_header_image'); ?>" width="1920" height="546" alt="">
    </picture>
    <header class="learnmore-header">
        <h1 class="learnmore-header__title"><?php echo translate('learn_more_header'); ?></h1>
        <p class="learnmore-header__paragraph"><?php echo translate('learn_more_header_subtext'); ?></p>
        <div class="btn btn-primary call-function call-action" data-js-action="modal:open-video-modal" data-title="<?php echo translate('learn_more_header_modal_title', null, true); ?>" data-href="JmGALfl5uOw" data-autoplay="true" <?php echo addQaUniqueIdentifier("page__learn_more__watch_our_video_btn"); ?>>
            <?php echo translate('learn_more_watch_video_btn'); ?>
        </div>
    </header>
</div>
