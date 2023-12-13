
<div class="js-modal-flex wr-modal-flex inputs-40">
	<div class="modal-flex__form">
		<div class="modal-flex__content" id="purchase-order-details--form--wrapper">
			<?php if ($is_confirmed) { ?>
				<ul class="nav nav-tabs nav--borders" role="tablist">
					<li class="nav-item">
						<a class="nav-link active" href="#purchase-order-details--form--details" aria-controls="title" role="tab" data-toggle="tab">Details</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#purchase-order-details--form--discussion" aria-controls="title" role="tab" data-toggle="tab">Discussion</a>
					</li>
				</ul>

				<div class="tab-content">
					<div role="tabpanel" class="tab-pane tab-pane-submit fade show active" id="purchase-order-details--form--details">
						<?php views()->display('new/order/po_form/content_view'); ?>
					</div>
					<div role="tabpanel" class="tab-pane tab-pane-submit fade show" id="purchase-order-details--form--discussion">
						<div class="container-fluid-modal">
							<div class="row">
								<div class="col-12">
									<div class="minfo-sidebar-ttl mt-15 mb-15">
										<span class="minfo-sidebar-ttl__txt">General timeline</span>
									</div>
								</div>
								<div class="col-12">
									<?php views()->display('new/order/po_form/notes_list_view', array(
										'timeline' => arrayGet($order, 'purchase_order_timeline', array()),
									)); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			<?php } else { ?>
				<?php views()->display('new/order/po_form/content_view'); ?>
			<?php } ?>
		</div>

		<?php if ($show_seller_buttons || $show_buyer_buttons) { ?>
			<div class="modal-flex__btns">
				<div class="modal-flex__btns-left">
					<a class="btn btn-primary fancyboxValidateModal fancybox.ajax"
						href="<?php echo __SITE_URL?>order/popups_order/purchase_order_notes/<?php echo $order['id']; ?>"
						title="Discuss Purchase Order (PO)">
						Discuss <span class="d-xs-none">Purchase Order</span> (PO)
					</a>
				</div>

				<div class="modal-flex__btns-right">
					<?php if($show_seller_buttons) { ?>
						<a
							class="btn btn-primary fancyboxValidateStepsForm fancybox.ajax"
							href="<?php echo __SITE_URL?>order/popups_order/purchase_order/<?php echo $order['id']; ?>"
							data-title="Purchase Order (PO)">
							<span class="txt">Edit <span class="d-xs-none">Purchase Order</span> (PO)</span>
						</a>
					<?php } ?>

					<?php if ($show_buyer_buttons) { ?>
						<span
							class="btn btn-success confirm-dialog"
							data-message="Are you sure you want to confirm the Purchase Order?"
							data-callback="confirmPurchaseOrder"
							data-order="<?php echo $order['id']; ?>">
							Confirm <span class="d-xs-none">Purchase Order</span> (PO)
						</span>
					<?php } ?>
				</div>
			</div>
		<?php }?>
	</div>
</div>

<script type="text/javascript">
	$(function(){
		var purchaseOrderTables = $('#purchase-order-details--form--wrapper .main-data-table');
		var orderedItemsTable = $('#purchase-order--ordered-items');
		var additionalItemsTable = $('#purchase-order--additional-items');

		var onOrientationChange = function () {
			normalizeTables();
			setTimeout(normalizeTables, 500);
		};

		var normalizeTables = function () {
			if(purchaseOrderTables.length !== 0){
				if($(window).width() < 768) {
					purchaseOrderTables.addClass('main-data-table--mobile');
				} else {
					purchaseOrderTables.removeClass('main-data-table--mobile');
				}
			}
		};

		mobileDataTable(purchaseOrderTables, false);
		normalizeTables();
	});

	<?php if ($show_buyer_buttons) { ?>
		var confirmPurchaseOrder = function (element) {
			var $this = $(element);
            var id_order = $this.data('order');
            var $wr = $this.closest('.js-modal-flex');

			$.ajax({
				type: 'POST',
				url: '<?php echo __SITE_URL?>order/ajax_order_operations/purchase_order_confirm',
				data: {id_order: id_order},
				beforeSend: function(){
					showLoader($wr);
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
						hideLoader(loader_wrapper);
					}
				}
			});
		}
	<?php } ?>
</script>
