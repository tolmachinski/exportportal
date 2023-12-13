<form id="method-form" class="validateModal">
    <input type="hidden" name="token" value="<?php echo $token; ?>">
	<div class="container-fluid-modal">
		<div class="row">
			<div class="col-12 tar">
				<span class="txt-medium"><?php echo translate('billing_documents_step3_total_amount_label_text', null, true); ?></span>
				$<span><?php echo get_price($total_amount, false); ?></span>
			</div>

			<div class="col-12 col-md-6">
				<label class="input-label input-label--required"><?php echo translate('pre_registration_page_register_form_user_label_fname')?></label>
                <input <?php echo addQaUniqueIdentifier("upgrade_payment_popup_credit-card_first-name_input")?> class="card-holder-first-name validate[required,minSize[2],maxSize[50],custom[validUserName]]" type="text" name="fname" />
			</div>

			<div class="col-12 col-md-6">
				<label class="input-label input-label--required"><?php echo translate('pre_registration_page_register_form_user_label_lname')?></label>
                <input <?php echo addQaUniqueIdentifier("upgrade_payment_popup_credit-card_last-name_input")?> class="card-holder-last-name validate[required,minSize[2],maxSize[50],custom[validUserName]]" type="text" name="lname" />
			</div>

			<div class="col-12 col-md-6">
				<label class="input-label input-label--required"><?php echo translate('billing_documents_step3_card_number');?></label>
                <div <?php echo addQaUniqueIdentifier("upgrade_payment_popup_credit-card_card-number_input")?> id="card-number"></div>
			</div>

			<div class="col-12 col-md-6">
				<div class="row">
					<div class="col-7">
						<label class="input-label input-label--required text-nowrap"><?php echo translate('billing_documents_step3_expiration_date');?></label>
						<div <?php echo addQaUniqueIdentifier("upgrade_payment_popup_credit-card_card-expiry_input")?> id="card-expiry"></div>
					</div>

					<div class="col-5">
						<label class="input-label input-label--required text-nowrap"><?php echo translate('billing_documents_step3_number_cvv');?></label>
						<div <?php echo addQaUniqueIdentifier("upgrade_payment_popup_credit-card_card-cvc_input")?> id="card-cvc"></div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<label class="input-label"><?php echo translate('label_note');?></label>
	<textarea <?php echo addQaUniqueIdentifier("upgrade_payment_popup_credit-card_note_textarea")?> class="validate[maxSize[500]] textcounter" data-max="500" name="note"></textarea>

	<label class="input-label"><?php echo translate('label_notation');?></label>
	<ul class="note-list pt-5">
		<?php foreach($notations as $note){?>
			<li><?php echo $note;?></li>
		<?php }?>
	</ul>

	<div id="card-errors"></div>
</form>
<script>
    $(function(){
        $('.textcounter').textcounter({
            countDown: true,
            countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
            countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
        });
    });
</script>
