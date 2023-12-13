<div class="container-fluid-modal mnh-200">
	<div class="row">
		<div class="col-12 tar">
			<span class="txt-medium"><?php echo translate('billing_documents_step3_total_amount_label_text', null, true); ?></span>
			$<span><?php echo get_price($total_amount, false); ?></span>
		</div>

		<div class="col-12 col-md-6 mnh-60">
			<div <?php echo addQaUniqueIdentifier("upgrade_payment_step-3_paypal_btn")?> id="payment-form--method--paypal-container"></div>
		</div>
	</div>

	<input type="hidden" name="pay_method" value="5"/>
	<label class="input-label"><?php echo translate('label_notation'); ?></label>
	<ul class="note-list pt-5">
		<?php foreach($notations as $note) { ?>
			<li><?php echo $note; ?></li>
		<?php } ?>
	</ul>
</div>
