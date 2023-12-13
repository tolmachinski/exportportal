<?php views()->display('new/payment/popup_pay_scripts_view'); ?>
<?php views()->display('new/file_upload_scripts'); ?>

<div class="js-modal-flex wr-modal-flex inputs-40">
	<div class="modal-flex__form">
		<div class="modal-flex__content">

			<div id="payment-form" class="js-order-payment-form order-payment-form">
				<div id="step-b-1" class="step-b">
					<?php views()->display('new/payment/step_1_payment_view'); ?>
				</div>
				<div id="step-b-2" class="step-b"></div>
				<div id="step-b-3" class="step-b"></div>
			</div>
		</div>

		<div class="js-btns-pay modal-flex__btns">
			<div class="modal-flex__btns-left">
				<div class="dropdown">
					<a <?php echo addQaUniqueIdentifier("upgrade_payment_popup_dropdown_btn")?> class="btn btn-light dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
						<i class="ep-icon ep-icon_menu-circles"></i>
					</a>

					<div class="dropdown-menu">
						<a <?php echo addQaUniqueIdentifier("upgrade_payment_popup_dropdown_download_btn")?>
                            class="dropdown-item"
							id="payment-form--action--download-invoice"
							href="<?php echo $invoice_link = __SITE_URL . "billing/invoice/{$bill_id}"; ?>"
							data-base-href="<?php echo $invoice_link; ?>"
							target="_blank">
							<i class="ep-icon ep-icon_download"></i> <?php echo translate('billing_documents_download_invoice');?>
						</a>
						<a <?php echo addQaUniqueIdentifier("upgrade_payment_popup_dropdown_requisites_btn")?>
                            class="dropdown-item"
							href="<?php echo __SITE_URL . "payments/bank_requisites"; ?>"
							target="_blank">
							<i class="ep-icon ep-icon_download"></i> <?php echo translate('billing_documents_step1_bank_requisites');?>
						</a>
					</div>
				</div>
			</div>
			<div class="modal-flex__btns-right">
				<button <?php echo addQaUniqueIdentifier("upgrade_payment_popup_back_btn")?> class="prev-btn btn btn-dark" style="display: none;"><?php echo translate('pagination_link_prev'); ?></button>
				<button <?php echo addQaUniqueIdentifier("upgrade_payment_popup_next_btn")?> class="next-btn btn btn-primary"><?php echo translate('pagination_link_next'); ?></button>
				<button <?php echo addQaUniqueIdentifier("upgrade_payment_popup_pay_btn")?> class="pay-btn btn btn-success" style="display: none;"><?php echo translate('billing_documents_step1_pay'); ?></button>
			</div>
		</div>
	</div>
</div>

<script src="https://www.paypal.com/sdk/js?client-id=<?php echo $paypal_key; ?>&disable-funding=card,credit&integration-date=2019-09-03" async defer></script>
<script>
	$(function() {
		if (!window.Stripe || !window.__set_stripe) {
			$.getScript("https://js.stripe.com/v3", function () {
				mix(window, { __set_stripe: true });
			});
		}
	});
</script>
