<div class="js-popup-container">
    <div id="js-popup-rating-question" class="popup-question" >
        <div class="popup-question__header">
            <h2 class="popup-blog__title"><?php echo translate('about_us_feedback_popup_ttl'); ?></h2>
            <a
                class="popup-blog__close ep-icon ep-icon_remove-stroke call-action"
                data-js-action="popups:close-hash-about-popup"
                href="#">
            </a>
        </div>
        <p class="popup-question__sub-title"><?php echo translate('about_us_feedback_popup_subttl'); ?></p>
        <div class="row">
            <div class="col-6 col-6 pr-7">
                <a
                    class="btn btn-light btn-block popup-question__button popup-question__button--like call-action"
                    data-js-action="popups:fancybox-hash-about-open"
                    data-mw="348"
                    data-href="#js-popup-question-like"
                    href="#">
                    <i class="ep-icon ep-icon_thumbup-stroke txt-blue2"></i><?php echo translate('about_us_feedback_popup_opinion_yes'); ?>
                </a>
            </div>
            <div class="col-6 col-6 pl-7">
                <a
                    class="btn btn-light btn-block popup-question__button popup-question__button--dislike call-action"
                    data-js-action="popups:fancybox-hash-about-open"
                    data-mw="348"
                    data-href="#js-popup-question-dislike"
                    href="#"
                >
                    <i class="ep-icon ep-icon_thumbdown-stroke txt-red"></i><?php echo translate('about_us_feedback_popup_opinion_no'); ?>
                </a>
            </div>
        </div>
    </div>

    <div
        id="js-popup-question-like"
        class="display-n"
    >
        <div class="popup-blog__heading">
            <h2 class="popup-blog__title"><?php echo translate('about_us_feedback_popup_form_ttl'); ?></h2>
            <a
                class="popup-blog__close ep-icon ep-icon_remove-stroke call-action"
                data-js-action="fancy-box:close"
                href="#">
            </a>
        </div>
        <p class="popup-question__sub-title"><?php echo translate('about_us_feedback_popup_form_subttl'); ?></p>
        <form
            class="js-popup-about-review-form"
            data-js-action="popups:submit-review-form"
            action="<?php echo __SITE_URL; ?>"
            method="POST"
        >
            <textarea class="js-textcounter popup-blog__sendtext" name="comment" placeholder="<?php echo translate('about_us_feedback_popup_form_subttl', null, true); ?>"></textarea>
            <input type="hidden" name="rating" value="1">
            <div class="clearfix pt-10">
                <button class="popup-button btn btn-primary mnw-170 pull-right" type="submit"><?php echo translate('about_us_feedback_popup_form_submit_btn'); ?></button>
            </div>
        </form>
    </div>

    <div
        id="js-popup-question-dislike"
        class="display-n"
    >
        <div class="popup-blog__heading">
            <h2 class="popup-blog__title"><?php echo translate('about_us_feedback_popup_form2_ttl'); ?></h2>
            <a
                class="popup-blog__close ep-icon ep-icon_remove-stroke call-action"
                data-js-action="fancy-box:close"
                href="#">
            </a>
        </div>
        <p class="popup-question__sub-title"><?php echo translate('about_us_feedback_popup_form2_subttl'); ?></p>
        <form
            class="js-popup-about-review-form"
            data-js-action="popups:submit-review-form"
        >
            <input type="hidden" name="rating" value="0">
            <textarea class="js-textcounter popup-blog__sendtext" name="comment" placeholder="<?php echo translate('about_us_feedback_popup_form2_placeholder', null, true); ?>"></textarea>
            <div class="clearfix pt-10">
                <button class="popup-button btn btn-primary mnw-170 pull-right" type="submit"><?php echo translate('about_us_feedback_popup_form_submit_btn'); ?></button>
            </div>
        </form>
    </div>

    <?php
        echo dispatchDynamicFragment(
            "popup:hash_about",
            null,
            true
        );
    ?>
</div>

