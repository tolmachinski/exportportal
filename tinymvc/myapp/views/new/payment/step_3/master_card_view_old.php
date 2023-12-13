<form id="method-form" class="validateModal">
	<div class="container-fluid-modal">
		<div class="row">
			<div class="col-12 tar">
				<span class="txt-medium"><?php echo translate('billing_documents_step3_total_amount_label_text', null, true); ?></span>
				$<span><?php echo get_price($total_amount, false); ?></span>
			</div>

			<div class="col-12 col-md-6">
				<label class="input-label input-label--required"><?php echo translate('pre_registration_page_register_form_user_label_fname'); ?></label>
				<input class="validate[required,minSize[2],maxSize[50],custom[validUserName]]" type="text" name="fname" />
			</div>
			<div class="col-12 col-md-6">
				<label class="input-label input-label--required"><?php echo translate('pre_registration_page_register_form_user_label_lname'); ?></label>
				<input class="validate[required,minSize[2],maxSize[50],custom[validUserName]]" type="text" name="lname" />
			</div>
		</div>

		<div class="row">
			<div class="col-12 col-md-6">
				<label class="input-label input-label--required"><?php echo translate('billing_documents_step3_card_number'); ?></label>
				<input class="validate[required,minSize[16],maxSize[19]]" type="text" name="number_card" />
			</div>
			<div class="col-12 col-md-6">
				<div class="row">
					<div class="col-12 col-md-6">
						<label class="input-label input-label--required"><?php echo translate('billing_documents_step3_expiration_date'); ?></label>
						<input class="w-50 validate[required,custom[positive_integer],maxSize[2],min[1],max[12]]" type="text" name="month_expire" placeholder="MM"/>
						<span class="lh-40">/</span>
						<input class="w-50 validate[required,custom[positive_integer],maxSize[2],min[<?php echo date('y'); ?>],max[99]]" type="text" name="year_expire" placeholder="YY"/>
					</div>
					<div class="col-12 col-md-6">
						<label class="input-label input-label--required"><?php echo translate('billing_documents_step3_number_cvv'); ?></label>
						<input class="validate[required,custom[positive_integer], minSize[3],maxSize[4]]" type="text" name="number_cvv_cvc" />
					</div>
				</div>
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
