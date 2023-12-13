<div class="js-modal-flex wr-modal-flex">
	<form
        class="modal-flex__form validateModal inputs-40"
        data-js-action="navbar:external-feedback"
        data-callback="sellerPopupInviteFeedbackEmailFormCallBack"
    >
		<div class="modal-flex__content">
			<input class="validate[required,custom[noWhitespaces],custom[emailsWithWhitespaces],maxEmailsCount[15]]" type="text" name="emails" value="" placeholder="Insert email addresses"/>
			<p class="fs-12 txt-red">*Please use comma as email separators, only 15 per day, remain <?php echo $invite_count;?></p>

			<label class="input-label">Message</label>
			<textarea class="validate[required,maxSize[1000]] js-textcounter-email-message" name="message" data-max="500" placeholder="Message"></textarea>
		</div>
		<div class="modal-flex__btns">
			<div class="modal-flex__btns-right">
				<button class="btn btn-primary" type="submit">Send</button>
			</div>
		</div>
	</form>
</div>

<?php echo dispatchDynamicFragmentInCompatMode("popup:external-feedback-form", asset('public/plug/js/invite-external-feedback/external-feedback.js', 'legacy'), null, array('company/ajax_send_email/invite_external_feedback')); ?>
