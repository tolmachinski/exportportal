<form id="method-form" class="validateModal">
	<div class="container-fluid-modal">
		<div class="row">
			<div class="col-12 tar">
				<span class="txt-medium"><?php echo translate('billing_documents_step3_total_amount_label_text', null, true); ?></span>
				$<span><?php echo get_price($total_amount, false); ?></span>
			</div>

			<div class="col-12 col-md-6">
				<label class="input-label input-label--required"><?php echo translate('pre_registration_page_register_form_user_label_fname'); ?></label>
				<input class="validate[required,minSize2[],maxSize[50],custom[validUserName]]" type="text" name="fname" />
			</div>
			<div class="col-12 col-md-6">
				<label class="input-label input-label--required"><?php echo translate('pre_registration_page_register_form_user_label_lname'); ?></label>
				<input class="validate[required,minSize[2],maxSize[50],custom[validUserName]]" type="text" name="lname" />
			</div>
			<div class="col-12">
				<label class="input-label input-label--required"><?php echo translate('billing_documents_step3_transaction_id'); ?></label>
				<input class="validate[required]" type="text" name="transaction_id" />
			</div>

			<div class="col-12">
				<?php widgetFileUploader($fileupload, translate('billing_documents_step3_upload_payment', null, true), 'western-union', 'document'); ?>
			</div>
		</div>
	</div>

	<label class="input-label"><?php echo translate('label_note'); ?></label>
	<textarea class="validate[maxSize[500]]" name="note" ></textarea>

	<label class="input-label"><?php echo translate('label_notation'); ?></label>
	<ul class="note-list pt-5">
		<?php foreach($notations as $note) { ?>
			<li><?php echo $note; ?></li>
		<?php } ?>
	</ul>

	<input type="hidden" name="token" value="<?php echo $token; ?>">
</form>
