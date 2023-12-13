<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="modal-flex__form validateModal" data-callback="friendInviteFormSubmit" data-js-action="friend-invite-popup:submit">
		<div class="modal-flex__content">
			<?php global $tmvc;?>
			<input type="text" name="emails" class="validate[required,custom[noWhitespaces],custom[emailsWithWhitespaces],maxEmailsCount[15]]" value="" placeholder="Insert email addresses"/>
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

<?php
    echo dispatchDynamicFragmentInCompatMode(
        "popup:invite-customers-popup",
        asset('public/plug/js/popups/invite-friends/index.js', 'legacy')
    );
?>
