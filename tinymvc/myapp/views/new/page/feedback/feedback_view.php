
<script>
function scrollToLeaveFeedback($this){
    scrollToElement('#js-feedback-form-wr', 0, 500);
}
</script>
<div class="feedback-header">
    <h1 class="feedback-main-title"><?php echo translate('feedback_header_title');?></h1>
    <p class="feedback-main-paragraph"><?php echo translate('feedback_header_subtitle');?></p>
    <a class="feedback-header__btn btn btn-primary call-function" data-callback="scrollToLeaveFeedback" href="#">Leave a feedback</a>

    <div class="feedback-header__bg">
        <img class="image" src="<?php echo __IMG_URL;?>public/img/feedback_page/hd-background.jpg" alt="Leave a feedback">
    </div>
</div>

<div id="js-feedback-form-wr" class="feedback-footer footer-connect">
    <h2 class="feedback-main-title feedback-main-title--lh-72"><?php echo translate('feedback_form_title');?></h2>
    <p class="feedback-main-paragraph"><?php echo translate('feedback_form_subtitle');?></p>

    <div class="container-center-sm">
        <form id="js-feedback-form" class="feedback-like-website-form" method="POST">
            <div class="feedback-like-website-form__buttons">
                <label class="like">
                    <input type="radio" class="like-input" name="rating" value="1">
                    <span class="feedback-like-website-form__button" <?php echo addQaUniqueIdentifier('page__feedback__hear-your-feedback_yes-btn') ?>>
                        <i class="ep-icon ep-icon_thumbup-stroke"></i>
                        <span class="feedback-like-website-form__button-hide-text">Yes</span>
                        <span class="feedback-like-website-form__button-text"><?php echo translate('feedback_form_button_like');?></span>
                    </span>
                </label>
                <label class="dislike">
                    <input type="radio" class="dislike-input" name="rating" value="0">
                    <span class="feedback-like-website-form__button" <?php echo addQaUniqueIdentifier('page__feedback__hear-your-feedback_no-btn') ?>>
                        <i class="ep-icon ep-icon_thumbdown-stroke"></i>
                        <span class="feedback-like-website-form__button-hide-text">No</span>
                        <span class="feedback-like-website-form__button-text"><?php echo translate('feedback_form_button_dislike');?></span>
                    </span>
                </label>
            </div>

            <label class="input-label"><?php echo translate('feedback_form_label_name');?></label>
            <input type="text" name="name" maxlength="100" <?php echo addQaUniqueIdentifier('page__feedback__hear-your-feedback_form_name-input') ?> placeholder="<?php echo translate('feedback_form_name_input_placeholder', null, true);?>">

            <label class="input-label"><?php echo translate('feedback_form_suggestions_label');?></label>
            <textarea class="js-textcounter-message" data-max="500" type="text" name="comment" <?php echo addQaUniqueIdentifier('page__feedback__hear-your-feedback_form_suggestion-textarea') ?> placeholder="<?php echo translate('feedback_form_suggestions_textarea_placeholder', null, true);?>"></textarea>

            <div class="feedback-like-website-form__actions">
                <button class="btn btn-primary mnw-270" <?php echo addQaUniqueIdentifier('page__feedback__hear-your-feedback_form_submit-btn') ?> type="submit"><?php echo translate('feedback_form_button_submit', null, true);?></button>
            </div>
        </form>
    </div>
</div>

<script>
    $(function () {
        $('#js-feedback-form').on('submit', function (e) {
            e.preventDefault();
            $form = $(this);

            $.ajax({
                type: "POST",
                url: __site_url + "popups/ajax_operations/save/feedback_page",
                data: $form.serialize(),
                dataType: "json",
                success: function (resp) {
                    if (resp.mess_type === 'error') {
                        return systemMessages(resp.message, 'warning');
                    }
                    systemMessages('<?php echo translate('feedback_successfully_message'); ?>', 'success');
                    $form.trigger('reset');
                }
            });
        });

        $('.js-textcounter-message').textcounter({
            countDown: true,
            countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
            countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
        });
    });
</script>
