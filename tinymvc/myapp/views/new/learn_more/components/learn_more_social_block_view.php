<div class="learnmore-social-block">
    <div class="container-center">
        <div class="learnmore-social-block__title">
            <h2 class="learnmore-ttl tac"><?php echo translate('learn_more_block_social_header'); ?></h2>
            <div class="learnmore-subttl tac"><?php echo translate('learn_more_block_social_header_subtext'); ?></div>
        </div>

        <div class="learnmore-socials">
            <?php if (null !== config('social_facebook')) { ?>
                <a class="learnmore-socials__item learnmore-socials__item--facebook" href="<?php echo config('social_facebook'); ?>" target="_blank">
                    <i class="ep-icon ep-icon_facebook"></i>
                </a>
            <?php } ?>
            <?php if (null !== config('social_twitter')) { ?>
                <a class="learnmore-socials__item learnmore-socials__item--twitter" href="<?php echo config('social_twitter'); ?>" target="_blank">
                    <i class="ep-icon ep-icon_twitter"></i>
                </a>
            <?php } ?>
            <?php if (null !== config('social_pinterest')) { ?>
                <a class="learnmore-socials__item learnmore-socials__item--pinterest" href="<?php echo config('social_pinterest'); ?>" target="_blank">
                    <i class="ep-icon ep-icon_pinterest"></i>
                </a>
            <?php } ?>
            <?php if (null !== config('social_linkedin')) { ?>
                <a class="learnmore-socials__item learnmore-socials__item--linkedin" href="<?php echo config('social_linkedin'); ?>" target="_blank">
                    <i class="ep-icon ep-icon_linkedin"></i>
                </a>
            <?php } ?>
            <?php if (null !== config('social_youtube')) { ?>
                <a class="learnmore-socials__item learnmore-socials__item--youtube" href="<?php echo config('social_youtube'); ?>" target="_blank">
                    <i class="ep-icon ep-icon_youtube"></i>
                </a>
            <?php } ?>
            <?php if (null !== config('social_instagram')) { ?>
                <a class="learnmore-socials__item learnmore-socials__item--instagram" href="<?php echo config('social_instagram'); ?>" target="_blank">
                    <i class="ep-icon ep-icon_instagram"></i>
                </a>
            <?php } ?>
            <a class="learnmore-socials__item learnmore-socials__item--whatsapp" href="https://wa.me/+<?php echo get_only_number(config('ep_phone_whatsapp')); ?>">
                <i class="ep-icon ep-icon_whatsapp"></i>
            </a>
        </div>
    </div>
</div>
