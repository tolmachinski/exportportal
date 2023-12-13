<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="modal-flex__form validateModal" data-js-action="navbar:friend-invited" data-callback="sendEmailInvite">
		<div class="modal-flex__content">
			<label class="input-label">Subject</label>
			<p class="lh-22">Invite to Connect | Export Portal</p>
			<label class="input-label input-label--required">
				<span class="input-label__text">Email</span>
				<a class="info-dialog ep-icon ep-icon_info"
					data-message="Please add the email address of the person you will be sending this message to."
					data-title="Friend’s email"
					href="#">
				</a>
			</label>
			<input
				type="text"
				name="email"
				class="w-50pr-md-min validate[required,custom[noWhitespaces],custom[emailWithWhitespaces]]"
				value=""
				placeholder="Your friend’s email"/>

				<label class="input-label input-label--required">
					<span class="input-label__text">Message</span>
					<a class="info-dialog ep-icon ep-icon_info"
						data-message="This is a preview of the message you’ll be sending to the recipient. Please note you will not be able to adjust this message."
						data-title="Your message"
						href="#">
					</a>
				</label>
			<textarea
					name="message"
					class="validate[required,maxSize[500]] js-textcounter-message"
					data-max="500"
					placeholder="Your message"><?php echo $invite_message;?>
			</textarea>
		</div>
		<div class="modal-flex__btns">
			<div class="modal-flex__btns-right">
				<button class="btn btn-primary" type="submit">Invite</button>
			</div>
		</div>
	</form>
</div>

<script type="text/template" id="js-template-email-invite-success">
	<div class="success-alert-b friend-invite__success">
		<div>
			<i class="ep-icon ep-icon_ok-circle"></i>
			<span class="friend-invite__success-text">
				<span class="friend-invite__success-thank_txt">Thank you!</span> Your invite has been sent to:
			</span>
			<div class="friend-invite__success-email">{{email}}</div>
		</div>
		<div class="d-flex">
			<span class="friend-invite__success-wrong_email">Wrong Email?</span>
			<a class="friend-invite__success-contact_us" href="<?php echo __SITE_URL . 'contact' ?>">Contact Us</a>
		</div>
	</div>
</script>

<?php echo dispatchDynamicFragmentInCompatMode(
    "popup:friend-invite-form",
    asset('public/plug/js/friend-invite/invite-by-email.js', 'legacy'),
    null,
    [getUrlForGroup('/invite/ajax_send_email/email')],
    true
); ?>
