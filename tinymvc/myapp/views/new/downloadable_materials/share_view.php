<div class="dwn-share-form js-modal-flex inputs-40">
    <form class="modal-flex__form validateModal" data-js-action="user_register:share_submit" data-callback="submitDMShareModal">
        <label class="input-label input-label--required mt-0"><?php echo translate('dwn_send_to_email') ?></label>
        <div class="dwn-share-form__input-group">
            <input class="validate[required,custom[noWhitespaces],custom[emailWithWhitespaces],maxSize[100]]"
                type="text"
                name="email"
                placeholder="Enter their email address">
            <button class="btn btn-primary pl-40 pr-40" type="submit"><?php echo translate('form_button_submit_text') ?></button>
        </div>
        <input type="hidden" name="slug" value="<?php echo $content['slug'] ?>">
    </form>
</div>

<div class="dwn-socials__label"><?php echo translate('dwn_send_using_socials') ?></div>
<div class="dwn-socials">
    <a href="#"
        data-social="facebook"
        data-title="<?php echo $content['title'] ?>"
        data-url="<?php echo $content['share_url'] ?>"
        class="dwn-socials__link dwn-socials__link--facebook call-action call-function"
        data-js-action="user_share:socials"
        data-callback="popup_share">
        <i class="ep-icon ep-icon_facebook"></i>
    </a>
    <a href="#"
        data-social="twitter"
        data-title="<?php echo $content['title'] ?>"
        data-url="<?php echo $content['share_url'] ?>"
        class="dwn-socials__link dwn-socials__link--twitter call-action call-function"
        data-js-action="user_share:socials"
        data-callback="popup_share">
        <i class="ep-icon ep-icon_twitter"></i>
    </a>
    <a href="#"
        data-social="linkedin"
        data-title="<?php echo $content['title'] ?>"
        data-url="<?php echo $content['share_url'] ?>"
        class="dwn-socials__link dwn-socials__link--linkedin call-action call-function"
        data-js-action="user_share:socials"
        data-callback="popup_share">
        <i class="ep-icon ep-icon_linkedin"></i>
    </a>
</div>

<?php echo dispatchDynamicFragmentInCompatMode("user_register:share", null, null, null, true); ?>
