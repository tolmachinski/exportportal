<div class="wr-modal-flex inputs-40">
	<div class="modal-flex__form">
		<div class="modal-flex__content">
			<div class="ep-tinymce-text">
				<p>Thank you for upgrading your account.<p>
				<ul>
					<li>
						Our specialists will <span class="txt-red">verify</span> the uploaded information as well as payment within <span class="txt-red">2-3 business days</span>.
					</li>
					<li>
						You will receive a <strong>confirmation email</strong> regarding your account upgrade status.
					</li>
					<li>
						After this, <strong>you will get access</strong> to new features and options.
					</li>
					<li>
						If you <strong>do not receive an email on time</strong>, please look for the message in your <strong>spam or junk</strong> mail folders.
					</li>
				</ul>

				<p>
					<?php echo translate('system_message_accreditation_verify_docs_help', array(
							'{{START_CONTACT_US_LINK}}' => '<a class="fancybox.ajax fancyboxValidateModal" data-before-callback="bootstrapDialogCloseAll" data-title="'. translate('help_contact_us') .'" href="'.__SITE_URL .'contact/popup_forms/contact_us">',
							'{{END_CONTACT_US_LINK}}' => '</a>'
					)); ?>
				</p>
			</div>

		</div>
		<div class="modal-flex__btns">
			<div class="modal-flex__btns-right">
				<a class="btn btn-primary call-function" data-callback="closeFancyBox">Ok</a>
			</div>
		</div>
	</div>
</div>
