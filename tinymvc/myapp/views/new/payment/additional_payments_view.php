<?php if(!empty($payments)) { ?>
	<div class="detail-info pt-20 pb-5 mb-0">
		<div class="detail-info__ttl pr-3">
			<h2 class="detail-info__ttl-name fs-14"><?php echo translate('billing_documents_additional_payments_label_text', null, true); ?></h2>
			<i class="ep-icon ep-icon_remove-stroke call-function cur-pointer" data-callback="alsoPayBills"></i>
		</div>

		<div class="detail-info__toggle pt-10">
			<?php foreach($payments as $payment) { ?>
				<?php if($payment['id_bill'] != $bill_info['id_bill']) { ?>
					<div class="additional-item-pay flex-display flex-ai--c flex-jc--sb pt-5 pb-5">
						<div class="flex-display flex-ai--c">
							<label class="custom-checkbox">
								<input
									id="<?php echo $payment['id_bill'].'-'.$payment['name_type']?>"
									type="checkbox"
									class="js-enable-additional-payment"
									value="<?php echo $payment['id_type_bill'].'-'.$payment['name_type'].'-'.$payment['id_bill'];?>"
									data-bill="<?php echo $payment['id_bill']; ?>"
									data-value="<?php echo $payment['balance']; ?>">
								<span class="custom-checkbox__text">
									<?php echo translate('billing_documents_additional_payments_bill_text', array('[[NAME]]' => $payment['show_name'], '[[NUMBER]]' => orderNumber($payment['id_bill'])), true); ?>
								</span>
							</label>

							<i class="ep-icon ep-icon_info-stroke ml-5 txt-gray cur-pointer" data-toggle="popover" data-placement="top" data-content="<?php echo cleanOutput($payment['bill_description']); ?>"></i>
						</div>

						<strong class="tar">$<?php echo get_price($payment['balance'], false); ?></strong>
					</div>
				<?php } ?>
			<?php } ?>
		</div>
	</div>

	<script>
		var alsoPayBills = function($this) {
			$this.toggleClass('ep-icon_plus-stroke ep-icon_remove-stroke');
			$('.detail-info__toggle').toggleClass('display-n');
		}
	</script>
<?php } ?>

