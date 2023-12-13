<h3 class="payment-form__title">
	<span class="payment-form__text"><?php echo translate('billing_documents_step1_label_text', null, true); ?></span>
</h3>

<div class="pt-10 flex-display flex-ai--c">
	<span class="tt-capitalize pr-5"><?php echo cleanOutput($bill_info['name_type']); ?></span> bill
	<span class="pl-5"><?php echo cleanOutput($bill_info['title']); ?></span>
	<i class="ml-10 ep-icon ep-icon_info-stroke txt-gray cur-pointer"
		data-toggle="popover"
		data-content="<?php echo cleanOutput($bill_info['bill_description']); ?>"
		data-placement="top">
	</i>
</div>

<div class="flex-display flex-ai--c flex-jc--fe">
	<span class="pr-10 txt-gray"><?php echo translate('billing_documents_step1_amount_label_text', null, true); ?></span>
	<span class="tar">$<?php echo get_price($bill_info['price'], false); ?></span>
</div>

<?php echo !empty($content_payments) ? $content_payments : ''; ?>

<div class="tar pt-15">
	<span class="txt-medium"><?php echo translate('billing_documents_step1_total_amount_label_text', null, true); ?></span>
	$<span id="total-amount" data-total="<?php echo cleanOutput($bill_info['price']); ?>"><?php echo get_price($bill_info['price'], false); ?></span>
</div>

<script>
	$(function(){
		var additionalPayments = $('.js-enable-additional-payment');
		var invoiceLink = $('#payment-form--action--download-invoice');
		var additionalPaymentsIds = [];
		var onToggleAdditionalPayments = function(e) {
			var input = $(this);
			var id = input.data('bill');
			if (input.is(':checked')) {
				additionalPaymentsIds.push(id);
			} else {
				var idIndex = additionalPaymentsIds.indexOf(id);
				if (idIndex !== -1) {
					additionalPaymentsIds.splice(idIndex);
				}
			}

			if (additionalPaymentsIds.length) {
				invoiceLink.attr('href', invoiceLink.data('base-href') + '?' + $.param({ additional_bills: additionalPaymentsIds }));
			} else {
				invoiceLink.attr('href', invoiceLink.data('base-href'));
			}
		};

		additionalPayments.on('ifToggled', onToggleAdditionalPayments);
		$('[data-toggle="popover"]').popover({
			trigger: 'hover'
		});
	});
</script>
