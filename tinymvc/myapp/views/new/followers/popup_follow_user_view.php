<div class="js-modal-flex wr-modal-flex inputs-40">
	<form
        class="modal-flex__form validateModal"
        data-js-action="follow:user-popup-submit-form"
        data-callback="followersPopupFollowFormCallBack"
    >
		<div class="modal-flex__content">
            <div class="form-group">
                <label class="input-label input-label--required"><?php echo translate('follow_user_message_label');?></label>
                <textarea data-max="500" <?php echo addQaUniqueIdentifier('popup__follow__form_message-input') ?> class="validate[required,maxSize[500]] js-textcounter-follow-user-message" name="message" placeholder="<?php echo translate('follow_user_message_label', null, true);?>"><?php if(!empty($text)) echo $text;?></textarea>
		    </div>

			<input type="hidden" value="<?php echo $id_user?>" name="user" />
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" <?php echo addQaUniqueIdentifier('popup__follow__form_send-btn') ?> type="submit"><?php echo translate('follow_user_form_submit_btn');?></button>
            </div>
		</div>
	</form>
</div>

<?php
    echo dispatchDynamicFragmentInCompatMode(
        "follow:user-popup",
        asset('public/plug/js/follow-popup/index.js', 'legacy'),
        null,
        array('followers/ajax_followers_operation/follow_user'),
        true
    );
?>
