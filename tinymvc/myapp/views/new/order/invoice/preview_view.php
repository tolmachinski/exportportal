<div class="js-modal-flex wr-modal-flex inputs-40">
	<div class="modal-flex__form">
		<div class="modal-flex__content">
            <div class="w-1024">
                <?php app()->view->display('new/order/invoice/content_view');?>
            </div>
        </div>
        <?php if($show_seller_buttons || $show_buyer_buttons){?>
            <div class="modal-flex__btns">
				<div class="modal-flex__btns-right">
					<?php if($show_seller_buttons){?>
						<span
							class="btn btn-success confirm-dialog"
							data-message="Are you sure you want to send the Invoice to Buyer for confirmation?"
							data-callback="sendInvoice"
							data-order="<?php echo $order['id'];?>">
							Send invoice to Buyer
						</span>
					<?php }?>

					<?php if($show_buyer_buttons){?>
						<span
							class="btn btn-success confirm-dialog"
							data-message="Are you sure you want to confirm the Invoice?"
							data-callback="confirmInvoice"
							data-order="<?php echo $order['id'];?>">
							Confirm Invoice
						</span>
					<?php }?>
				</div>
            </div>
        <?php }?>
	</div>
</div>

<script type="text/javascript">
<?php if($show_buyer_buttons){?>
	var confirmInvoice = function(element){
		var $this = $(element);
		var id_order = $this.data('order');
		var $wrapper = $this.closest('.js-modal-flex');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>invoices/ajax_invoice_options/confirm_invoice',
			data: {id_order: id_order},
			beforeSend: function(){
				$this.prop('disabled', true);
				showLoader($wrapper);
			},
			dataType: 'json',
			success: function(resp){
				systemMessages( resp.message, resp.mess_type );

				if(resp.mess_type == 'success'){
					current_status = resp.order_status_alias;
					loadOrderList(true);
					showOrder(resp.order);
					update_status_counter_active(resp.order_status_name, current_status);

					closeFancyBox();
				} else{
					$this.prop('disabled', false);
					hideLoader($wrapper);
				}
			}
		});
	}
<?php }?>

<?php if($show_seller_buttons){?>
	var sendInvoice = function(element){
		var $this = $(element);
		var id_order = $this.data('order');
		var $wrapper = $this.closest('.js-modal-flex');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>invoices/ajax_invoice_options/send_invoice',
			data: {id_order: id_order},
			beforeSend: function(){
				showLoader($wrapper);
				$this.prop('disabled', true);
			},
			dataType: 'json',
			success: function(resp){
				systemMessages( resp.message, resp.mess_type );

				if(resp.mess_type == 'success'){
					current_status = resp.order_status_alias;
					loadOrderList(true);
					showOrder(resp.order);
					update_status_counter_active(resp.order_status_name, current_status);

					closeFancyBox();
				} else{
					$this.prop('disabled', false);
					hideLoader($wrapper);
				}
			}
		});
	}
<?php }?>
</script>
