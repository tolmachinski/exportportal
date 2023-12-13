<?php $cookie = tmvc::instance()->controller->cookies; ?>
<?php $order_cancel_status = array('Late payment', 'Canceled by buyer', 'Canceled by seller', 'Canceled by EP');?>

<div id="order-detail-<?php echo $order['id']?>" class="order-detail">
	<div class="order-detail__scroll">
		<div class="order-detail__top">
			<ul class="order-detail__params">
				<li class="order-detail__params-item order-detail__params-item--double">
					<div class="order-detail__param-col">
						<div class="order-detail__params-name">Number:</div>
						<div class="order-detail__number"><?php echo orderNumber($order['id']);?></div>
					</div>
					<div class="order-detail__param-col">
						<div class="order-detail__params-name">Status:</div>
						<div class="order-detail__status<?php echo (in_array($order['status'], $order_cancel_status))?' txt-red':'';?>">
							<span class="info-dialog-100 cur-pointer" data-title="What's next in <span class='txt-gray'><?php echo $order['status'];?></span>" data-content="#js-hidden-status-text" data-actions="#js-hidden-status-actions"><?php echo $order['status'];?></span>
						</div>

						<?php if(!empty($description_title)){?>
						<div class="display-n">
							<div id="js-hidden-status-text">
								<div class="order-status__content">
								<?php if(!empty($description_title['mandatory'])){?>
									<?php echo $description_title['mandatory'];?>
								<?php }?>

								<?php if(!empty($description_title['optional'])){?>
									<h3 class="order-status__ttl">Optional</h3>
									<?php echo $description_title['optional'];?>
								<?php }?>
								</div>
							</div>

							<div id="js-hidden-status-actions">
								<?php if(!cookies()->exist_cookie('_ep_view_order_status')){?>
									<div class="js-order-status-modal inputs-40 flex-ai--c order-status__btns">
										<label class="custom-checkbox">
											<input class="js-dont-show-more" type="checkbox" name="dont_show_more">
											<span class="custom-checkbox__text">Don't show more</span>
										</label>

										<a class="btn btn-dark js-btn-close w-130" href="#">Ok</a>
									</div>
								<?php }?>
							</div>
						</div>
						<?php }?>
					</div>
				</li>
				<li class="order-detail__params-item">
					<div class="order-detail__params-name">Remaining:</div>
					<div class="order-detail__time">
						<?php if($order['cancel_request'] == 1){?>
							<div class="lh-20 txt-red">There are cancel request for this order. Please check the
								<span
									class="link-ajax info-dialog-ajax"
									data-href="<?php echo getUrlForGroup('order/popups_order/order_timeline/'.$order['id']);?>"
									data-title="Order <?php echo orderNumber($order['id']);?> timeline">order timeline</span>
								for more info.
							</div>
						<?php } else{?>
							<div class="order-detail__status-timer"></div>
						<?php }?>
					</div>
				</li>
			</ul>

			<?php if(!empty($extend_info)){?>
				<div class="warning-alert-b mt-15">
					<i class="ep-icon ep-icon_warning-circle-stroke"></i>
					<span>
						<?php if(
								!empty($extend_info)
								&& $extend_info['status_seller'] == 'approved'
								&& $extend_info['status_buyer'] == 'approved'
								&& $extend_info['status_shipper'] == 'approved'
							){?>
							All parties accepted the time extension for this order. Please wait until it is updated.
						<?php }else{?>
							<div>
							<?php if(
									!empty($extend_info)
									&& (
										(have_right('manage_seller_orders') && $extend_info['status_seller'] == 'awaiting')
										|| (have_right('buy_item') && $extend_info['status_buyer'] == 'awaiting')
										|| (have_right('manage_shipper_orders') && $extend_info['status_shipper'] == 'awaiting')
									)
								){?>
								Please confirm that <strong>You</strong> agree to extend the time for this Order,
								<a class="call-function txt-medium" data-callback="openModalAutoExtend" href="#">here</a>.
							<?php }else{?>
								<strong>You</strong> have already agreed for Time Extension for this Order.
							<?php }?>
							</div>

							<div>
							<?php if(have_right('manage_seller_orders')){?>
								<?php if($extend_info['status_buyer'] == 'approved'){?>
									<div>The <strong>Buyer</strong> has already agreed with Time Extension for this Order.</div>
								<?php }else{?>
									<div>The <strong>Buyer</strong> should agree with Time Extension for this Order.</div>
								<?php }?>

								<?php if(
										$order['shipper_type'] == 'ep_shipper'
										&& (int)$order['id_shipper'] > 0
									){?>
									<?php if($extend_info['status_shipper'] == 'approved'){?>
										<div>The <strong>Freight Forwarder</strong> has already agreed with Time Extension for this Order.</div>
									<?php }else{?>
										<div>The <strong>Freight Forwarder</strong> should agree with Time Extension for this Order.</div>
									<?php }?>
								<?php }?>
							<?php }else if(have_right('buy_item')){?>
								<?php if($extend_info['status_seller'] == 'approved'){?>
									<div>The <strong>Seller</strong> has already agreed with Time Extension for this Order. </div>
								<?php }else{?>
									<div>The <strong>Seller</strong> should agree with Time Extension for this Order.</div>
								<?php }?>

								<?php if(
										$order['shipper_type'] == 'ep_shipper'
										&& (int)$order['id_shipper'] > 0
									){?>
									<?php if($extend_info['status_shipper'] == 'approved'){?>
										<div>The <strong>Freight Forwarder</strong> has already agreed with Time Extension for this Order.</div>
									<?php }else{?>
										<div>The <strong>Freight Forwarder</strong> should agree with Time Extension for this Order.</div>
									<?php }?>
								<?php }?>
							<?php }else if(have_right('manage_shipper_orders')){?>
								<?php if($extend_info['status_seller'] == 'approved'){?>
									<div>The <strong>Seller</strong> has already agreed with Time Extension for this Order. </div>
								<?php }else{?>
									<div>The <strong>Seller</strong> should agree with Time Extension for this Order.</div>
								<?php }?>

								<?php if($extend_info['status_buyer'] == 'approved'){?>
									<div>The <strong>Buyer</strong> has already agreed with Time Extension for this Order.</div>
								<?php }else{?>
									<div>The <strong>Buyer</strong> should agree with Time Extension for this Order.</div>
								<?php }?>
							<?php }?>

							</div>
						<?php }?>

					</span>
				</div>
			<?php }?>

			<div class="order-detail__top-btns">
				<div class="order-detail__top-btns-item">
					<a class="btn btn-light btn-block info-dialog-100" data-title="What's next in <span class='txt-gray'><?php echo $order['status'];?></span>"  data-content="#js-hidden-status-text" data-actions="#js-hidden-status-actions" href="#">
						<i class="ep-icon ep-icon_info txt-gray fs-16"></i>
						<span class="pl-5">What's next</span>
					</a>
				</div>

				<?php if(have_right('manage_messages')){?>
				<div class="order-detail__top-btns-item">
					<div class="dropdown">
						<a class="btn btn-light btn-block dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i class="ep-icon txt-gray ep-icon_chat2 fs-16"></i>
							<span class="pl-5 pr-5">Write to</span>
							<i class="ep-icon ep-icon_arrow-down fs-9"></i>
						</a>

						<div class="dropdown-menu dropdown-menu-right">
							<?php echo !empty($btnChatSeller) ? $btnChatSeller : ''; ?>
							<?php echo !empty($btnChatBuyer) ? $btnChatBuyer : ''; ?>
							<?php echo !empty($btnChatShipper) ? $btnChatShipper : ''; ?>
							<?php echo !empty($btnChatManager) ? $btnChatManager : ''; ?>
						</div>
					</div>
				</div>
				<?php }?>

				<div class="order-detail__top-btns-item">
					<div class="dropdown">
						<a class="btn btn-primary btn-block dropdown-toggle<?php echo ($order['status_alias'] == 'new_order')?' pulse-shadow-animation':'';?>" data-toggle="dropdown" data-flip="false" aria-haspopup="true" aria-expanded="false">
							<i class="ep-icon ep-icon_menu-circles"></i>
							<span class="pl-5">Action on order</span>
						</a>

						<div class="dropdown-menu dropdown-menu-right">

							<?php if (
									$expire > 0
									&& in_array($order['status_alias'], array('shipping_in_progress', 'shipping_completed'))
									&& !empty($order_disputes[0])
									&& (have_right('manage_disputes') && $order_disputes[0]['status'] == 'processing' || have_right('buy_item'))
							){ ?>
								<span
									class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax"
									data-title="Dispute timeline"
									data-fancybox-href="<?php echo getUrlForGroup('dispute/popup_forms/add_notice/'. $order_disputes[0]['id']);?>">
									<i class="ep-icon ep-icon_low"></i><span class="txt">Dispute timeline</span>
								</span>
								<span class="dropdown-item link-ajax fancybox.ajax fancyboxValidateModal"
									data-fancybox-href="<?php echo getUrlForGroup("dispute/popup_forms/edit/{$order_disputes[0]['id']}"); ?>"
									data-title="Discuss on dispute"
									title="Discuss on dispute">
									<i class="ep-icon ep-icon_pencil"></i><span>Discuss on dispute</span>
								</span>
							<?php } ?>

							<?php if(have_right('manage_shipper_orders') && $order['status_alias'] == 'shipping_ready_for_pickup' && (int)$order['shipper_confirm_delivery'] == 0 && $expire > 0){?>
								<a
									class="dropdown-item confirm-dialog"
									title="Mark as delivered"
									data-message="Are you sure you want to mark order as delivered?"
									data-callback="confirm_delivery"
									data-order="<?php echo $order['id']; ?>" href="#"
									><i class="ep-icon ep-icon_ok-circle"></i><span class="txt">Mark as delivered</span>
								</a>
							<?php }?>

							<?php if(
								have_right('manage_shipper_orders')
								&& $order['status_alias'] == 'shipping_in_progress'
							){?>
								<span
									class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax"
									data-fancybox-href="<?php echo getUrlForGroup('order/popups_order/add_date_ready_for_pickup/'.$order['id']);?>"
									data-title="Confirm ready for pickup"
									data-mw="400"
								><i class="ep-icon ep-icon_ok-circle"></i><span class="txt">Ready for pick up</span>
								</span>
							<?php }?>

							<?php if(have_right('manage_shipper_orders') && $order['status_alias'] == 'preparing_for_shipping'){?>
								<span
									class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax"
									data-title="Add tracking info"
									title="Add tracking info"
									data-fancybox-href="<?php echo getUrlForGroup('order/popups_order/add_tracking_info/' . $order['id']);?>"
								><i class="ep-icon ep-icon_box-close"></i><span class="txt">Add tracking info</span>
								</span>
							<?php }?>

							<?php if(have_right('manage_shipper_orders') && $order['status_alias'] == 'shipping_in_progress'){?>
								<span
									class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax"
									data-title="Edit tracking info"
									title="Edit tracking info"
									data-fancybox-href="<?php echo getUrlForGroup('order/popups_order/edit_tracking_info/' . $order['id']);?>"
									><i class="ep-icon ep-icon_pencil"></i><span class="txt">Edit tracking info</span>
								</span>
							<?php }?>


							<?php if((have_right('buy_item') || have_right('manage_seller_orders') ) && ($order['status_alias'] == 'order_completed')){
								if(empty($order_feedbacks)){?>
									<span
										class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax"
										data-fancybox-href="<?php echo getUrlForGroup('feedbacks/popup_forms/add_feedback/order/'.$order['id']);?>"
										data-dashboard-class="inputs-40"
										data-title="Add feedback"
									><i class="ep-icon ep-icon_diamond-stroke"></i><span class="txt">Add feedback</span>
								</span>
								<?php }elseif(count($order_feedbacks) > 1){?>
									<span
										class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax"
										data-fancybox-href="<?php echo getUrlForGroup('order/popups_order/feedbacks/'.$order['id']);?>"
										data-dashboard-class="inputs-40"
										data-title="Feedback(s)"><i class="ep-icon ep-icon_diamond-stroke txt-blue2"></i><span class="txt">Feedback(s)</span>
									</span>
								<?php }elseif($order_feedbacks[0]['id_poster'] == id_session()){?>
									<span
										class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax"
										data-fancybox-href="<?php echo getUrlForGroup('order/popups_order/feedbacks/'.$order['id']);?>"
										data-dashboard-class="inputs-40"
										data-title="Feedback(s)"
										><i class="ep-icon ep-icon_diamond-stroke txt-orange"></i><span class="txt">Feedback(s)</span>
									</span>
								<?php }else{?>
									<span
										class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax"
										data-fancybox-href="<?php echo getUrlForGroup('order/popups_order/feedbacks/'.$order['id']);?>"
										data-dashboard-class="inputs-40"
										data-title="Feedback(s)"
									>
										<i class="ep-icon ep-icon_diamond-stroke txt-orange"></i><span class="txt">Feedback(s)</span>
									</span>
								<?php }?>
							<?php }?>

							<?php if($order['status_alias'] == 'shipping_completed' && have_right('buy_item') && $expire > 0){?>
								<a class="dropdown-item confirm-dialog pulse-shadow-animation" href="#" data-order="<?php echo $order['id']; ?>" data-message="Are you sure you want to confirm that the order is completed?" data-callback="confirm_order_completed"><i class="ep-icon ep-icon_ok-circle"></i><span class="txt">Order completed</span></a>
							<?php }?>

							<?php if($order['status_alias'] == 'shipping_ready_for_pickup' && have_right('buy_item') && $expire > 0){?>
								<a class="dropdown-item confirm-dialog pulse-shadow-animation" href="#" data-order="<?php echo $order['id']; ?>" data-message="Are you sure you want to confirm the delivery?" data-callback="confirm_shipping_complete"><i class="ep-icon ep-icon_truck"></i><span class="txt">Confirm delivery</span></a>
							<?php }?>

							<?php if($order['shipper_type'] == 'ishipper'){?>
								<?php if($order['status_alias'] == 'shipping_in_progress' && have_right('manage_seller_orders') && $expire > 0){?>
									<span
										class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax pulse-shadow-animation"
										data-fancybox-href="<?php echo getUrlForGroup('order/popups_order/add_date_ready_for_pickup/'.$order['id']);?>"
										data-title="Confirm ready for pickup"
										data-mw="400"
									><i class="ep-icon ep-icon_ok-circle"></i><span class="txt">Confirm ready for pickup</span>
									</span>

									<span
										class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax"
										data-title="Update tracking info"
										title="Update tracking info"
										data-fancybox-href="<?php echo getUrlForGroup('order/popups_order/edit_tracking_info/'.$order['id']);?>"
									><i class="ep-icon ep-icon_pencil"></i><span class="txt">Update tracking info</span>
									</span>
								<?php }?>

								<?php if($order['status_alias'] == 'preparing_for_shipping' && have_right('manage_seller_orders') && $expire > 0){?>
									<span
										class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax"
										data-title="Finish packaging and add tracking info"
										title="Finish packaging and add tracking info"
										data-fancybox-href="<?php echo getUrlForGroup('order/popups_order/add_tracking_info/'.$order['id']);?>"
									><i class="ep-icon ep-icon_box-close"></i><span class="txt">Finish packaging</span>
									</span>
								<?php }?>
							<?php }?>

							<?php if($order['status_alias'] == 'payment_confirmed' && have_right('manage_seller_orders') && $expire > 0){?>
								<a class="dropdown-item confirm-dialog" href="#" data-order="<?php echo $order['id']; ?>" data-message="Are you sure you want to start preparing item(s) for shipping?" data-callback="start_packaging"><i class="ep-icon ep-icon_box fs-20"></i><span class="txt">Start preparing for shipping</span></a>
							<?php }?>

							<?php if(have_right('buy_item') && in_array($order['status_alias'], array('shipper_assigned','payment_processing','order_paid')) && $bills_counter && $expire > 0){?>
								<span
									class="dropdown-item link-ajax fancybox fancybox.ajax <?php echo 'shipper_assigned' === $order['status_alias'] ? "pulse-shadow-animation" : ''; ?>"
									data-fancybox-href="<?php echo getUrlForGroup('order/popups_order/bills_list/'.$order['id']);?>"
									data-dashboard-class="inputs-40"
									data-title="The order <?php echo orderNumber($order['id']);?> bills list"
								>
									<i class="ep-icon ep-icon_dollar-circle"></i><span class="txt">Bills list</span>
								</span>
							<?php }?>

							<?php if($order['status_alias'] == 'invoice_confirmed' && $expire > 0){?>
								<?php if(have_right('manage_seller_orders')){?>
									<span
										class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax"
										data-fancybox-href="<?php echo getUrlForGroup('order/popups_order/ishipper_quotes/'.$order['id']);?>"
										data-dashboard-class="inputs-40"
										data-w="95%"
										data-title="Setup International freight forwarders' rates"
									><i class="ep-icon ep-icon_statistic"></i><span class="txt">Setup International freight forwarders' rates</span>
									</span>
								<?php }?>

								<span
									class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax"
									data-fancybox-href="<?php echo getUrlForGroup('order/popups_order/order_shipping_quotes/'.$order['id']);?>"
									data-dashboard-class="inputs-40"
									data-title-type="html"
									data-title="Shipping rates <?php echo htmlspecialchars('<div class="elem-powered-by pl-10"><div class="elem-powered-by__txt">Powered by</div><div class="elem-powered-by__name">EPL</div></div>');?>"
									data-mw="900"
								><i class="ep-icon ep-icon_statistic"></i><span class="txt">Shipping rates</span>
								</span>
							<?php }?>

							<?php if(in_array($order['status_alias'], array('invoice_sent_to_buyer','invoice_confirmed')) && $expire > 0){?>
								<?php $invoice_order_details_title = 'invoice_sent_to_buyer' === $order['status_alias'] && have_right('buy_item') ? "Confirm Invoice" : "Invoice details";?>
								<span
									class="dropdown-item link-ajax fancybox fancybox.ajax"
									data-w="1024"
									data-mw="1024"
									data-fancybox-href="<?php echo getUrlForGroup('invoices/popups_invoice/view_invoice/'.$order['id']);?>"
									data-dashboard-class="inputs-40"
									data-title="<?php echo $invoice_order_details_title;?>"
								>
									<i class="ep-icon ep-icon_file"></i><span class="txt"><?php echo $invoice_order_details_title;?></span>
								</span>
							<?php }?>

							<?php if($order['status_alias'] == 'new_order' && have_right('buy_item') && empty($order['ship_to']) && $expire > 0){?>
								<span
									class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax"
									data-fancybox-href="<?php echo getUrlForGroup('order/popups_order/shipping_address/'.$order['id']);?>"
									data-dashboard-class="inputs-40"
									data-title="Ship to"
									><i class="ep-icon ep-icon_marker-stroke2"></i><span class="txt">Ship to</span>
								</span>
							<?php }?>

							<?php if($order['status_alias'] == 'purchase_order_confirmed' && have_right('manage_seller_orders') && $expire > 0){?>
								<span
									class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax pulse-shadow-animation"
									data-w="1024"
									data-mw="1024"
									data-fancybox-href="<?php echo getUrlForGroup('invoices/popups_invoice/view_invoice/'.$order['id']);?>"
									data-dashboard-class="inputs-40"
									data-title="Create Invoice"
								>
									<i class="ep-icon ep-icon_file-plus"></i><span class="txt">Create Invoice</span>
								</span>
							<?php }?>

							<?php $_show_purchase_order_divider = false;?>
							<?php if (have_right_or('buy_item,manage_seller_orders') && $expire > 0) { ?>
								<?php if (!in_array($order['status_alias'], array('new_order', 'canceled_by_buyer', 'canceled_by_seller', 'canceled_by_ep'))) { ?>
									<?php
										$purchase_order_details_title = 'purchase_order' === $order['status_alias'] && have_right('buy_item') ? "Confirm Purchase Order (PO)" : "Purchase Order (PO) details";
										$pulse_po_btn = 'purchase_order' === $order['status_alias'] && have_right('buy_item') ? 'pulse-shadow-animation' : '';
									?>
									<span
										class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax <?php echo $pulse_po_btn; ?>"
										data-fancybox-href="<?php echo getUrlForGroup('order/popups_order/view_purchase_order/'.$order['id']); ?>"
										data-w="95%"
										data-dashboard-class="inputs-40"
										data-title="<?php echo cleanOutput($purchase_order_details_title); ?>"
									>
										<i class="ep-icon ep-icon_file-text"></i><span class="txt"><?php echo cleanOutput($purchase_order_details_title); ?></span>
									</span>
									<?php $_show_purchase_order_divider = true; ?>
								<?php } ?>

								<?php if ($order['status_alias'] == 'purchase_order') { ?>
									<span
										class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax"
										data-fancybox-href="<?php echo getUrlForGroup('order/popups_order/purchase_order_notes/'.$order['id']);?>"
										data-w="95%"
										data-dashboard-class="inputs-40"
										data-title="Discuss Purchase Order (PO)"
									>
										<i class="ep-icon ep-icon_comments-stroke"></i><span class="txt">Discuss Purchase Order (PO)</span>
									</span>
									<?php $_show_purchase_order_divider = true; ?>
								<?php } ?>

								<?php if (have_right('manage_seller_orders') && in_array($order['status_alias'], array('new_order', 'purchase_order'))) { ?>
									<?php $manage_po_btn_text = $order['status_alias'] == 'new_order' ? 'Create Purchase Order (PO)' : 'Edit Purchase Order (PO)'; ?>
									<?php $pulse_po_btn = $order['status_alias'] == 'new_order' ? 'pulse-shadow-animation' : ''; ?>

									<span
										class="dropdown-item link-ajax fancyboxValidateStepsForm fancybox.ajax <?php echo $pulse_po_btn; ?>"
										data-fancybox-href="<?php echo getUrlForGroup('order/popups_order/purchase_order/'.$order['id']); ?>"
										data-dashboard-class="inputs-40"
										data-title="Purchase Order (PO)"
									>
										<i class="ep-icon ep-icon_pencil"></i><span class="txt"><?php echo $manage_po_btn_text; ?></span>
									</span>
									<?php $_show_purchase_order_divider = true; ?>
								<?php } ?>

								<?php if($_show_purchase_order_divider) { ?>
									<div class="dropdown-divider"></div>
								<?php } ?>
							<?php } ?>

							<?php if(($order['order_type'] == 'po') && ($bills_percent > 0) && in_array($order['status_alias'], array('payment_processing','order_paid','payment_confirmed')) && have_right('manage_seller_orders') && $expire > 0){?>
								<span
									class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax"
									data-fancybox-href="<?php echo getUrlForGroup('order/popups_order/producing_status/'.$order['id']);?>"
									data-title="Producing stage"
								><i class="ep-icon ep-icon_gears"></i><span class="txt">Producing stage</span>
								</span>
							<?php }?>

							<?php if(!in_array($order['status_alias'], array('order_completed', 'late_payment', 'canceled_by_buyer', 'canceled_by_seller', 'canceled_by_ep')) && $expire > 0){?>
								<span class="dropdown-item link-ajax info-dialog-100" data-title="How to use  <span class='txt-gray'>Order Documents?</span>" title="How to use Order Documents?" data-dashboard-class="inputs-40" data-content="#js-hidden-how-to-order-documents-text">
									<i class="ep-icon ep-icon_info-stroke"></i><span class="txt">How to use Order Documents?</span>
								</span>
							<?php }?>

							<?php if($documents_count > 0){?>
								<span
									class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax"
									data-fancybox-href="<?php echo getUrlForGroup("order_documents/popup_forms/list-envelopes/{$order['id']}"); ?>"
									title="View Order Documents"
									data-h="100%"
									data-w="99%"
									data-mw="900"
									data-title-type="html"
									data-title="Order <?php echo orderNumber($order['id']);?> documents list <?php echo htmlspecialchars('<div class="elem-powered-by pl-10"><div class="elem-powered-by__txt">Secured by</div><div class="elem-powered-by__name">EP Docs</div></div>');?>"
								>
									<i class="ep-icon ep-icon_folder"></i><span class="txt">View Order Documents</span>
								</span>
							<?php }?>

							<?php if(!in_array($order['status_alias'], array('order_completed', 'late_payment', 'canceled_by_buyer', 'canceled_by_seller', 'canceled_by_ep')) && $expire > 0){?>
								<span
									class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax"
									data-fancybox-href="<?php echo getUrlForGroup("order_documents/popup_forms/create-envelope/{$order['id']}"); ?>"
									title="Add Order Documents"
									data-dashboard-class="inputs-40"
									data-title-type="html"
									data-title="Add Order Document <?php echo htmlspecialchars('<div class="elem-powered-by pl-10"><div class="elem-powered-by__txt">Secured by</div><div class="elem-powered-by__name">EP Docs</div></div>');?>"
								>
									<i class="ep-icon ep-icon_plus-square"></i><span class="txt">Add Order Documents</span>
								</span>
							<?php }?>

							<div class="dropdown-divider"></div>

							<?php if($extend_btn){?>
								<span
									class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax <?php if($expire < 0){echo 'pulse-shadow-animation';}?>"
									data-fancybox-href="<?php echo getUrlForGroup('extend/popup_form/order/'.$order['id']);?>"
									data-dashboard-class="inputs-40"
									data-title="Extend status time"
								><i class="ep-icon ep-icon_clock-stroke2"></i><span class="txt">Extend status time</span>
								</span>
							<?php }?>

							<?php if($show_extend_btn){?>
								<span
									class="dropdown-item link-ajax fancybox fancybox.ajax <?php if($expire < 0){echo 'pulse-shadow-animation';}?>"
									data-fancybox-href="<?php echo getUrlForGroup('extend/popup_form/detail/'.$order['extend_request'])?>"
									data-title="Extend request details"
								><i class="ep-icon ep-icon_info-stroke"></i><span class="txt">Extend request detail</span>
								</span>
							<?php }?>

							<?php {?>
								<span
									class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax"
									data-title="Open dispute"
									data-fancybox-href="<?php echo getUrlForGroup('dispute/popup_forms/add/order/'.$order['id']);?>"
									data-dashboard-class="inputs-40"
								><i class="ep-icon ep-icon_low"></i><span class="txt">Open dispute</span>
								</span>
							<?php }?>

							<?php if(have_right('cancel_order_request') && !in_array($order['status_alias'], array('shipping_completed', 'shipping_in_progress', 'order_completed', 'late_payment', 'canceled_by_buyer', 'canceled_by_seller', 'canceled_by_ep'))){?>
								<?php if(in_array($order['status_alias'], array('payment_processing', 'payment_confirmed', 'preparing_for_shipping'))){?>
									<span
										class="js-btn-cancel-request dropdown-item link-ajax fancyboxValidateModal fancybox.ajax"
										data-fancybox-href="<?php echo getUrlForGroup('order/popups_order/cancel_request/'.$order['id']);?>"
										data-dashboard-class="inputs-40"
										data-title="Report a problem">
											<i class="ep-icon ep-icon_remove-circle"></i><span class="txt">Report a problem</span>
									</span>
								<?php }else{?>
									<span
										class="js-btn-cancel-request dropdown-item link-ajax fancyboxValidateModal fancybox.ajax"
										data-fancybox-href="<?php echo getUrlForGroup('order/popups_order/cancel_request/'.$order['id']);?>"
										data-dashboard-class="inputs-40"
										data-title="Cancel order request">
										<i class="ep-icon ep-icon_remove-circle"></i><span class="txt">Cancel order request</span>
									</span>
								<?php }?>
							<?php }?>

							<span
								class="dropdown-item link-ajax info-dialog-ajax"
								data-href="<?php echo getUrlForGroup('order/popups_order/order_timeline/'.$order['id']);?>"
								data-title="Order <?php echo orderNumber($order['id']);?> timeline">
									<i class="ep-icon ep-icon_hourglass"></i><span class="txt">View order timeline</span>
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="order-detail__scroll-padding">
			<?php if(in_array($order['status_alias'], array('shipping_in_progress', 'shipping_completed')) && $order['dispute_opened'] == 1){?>
				<div class="error-alert-b mb-15">
					<i class="ep-icon ep-icon_low"></i> <span><a href="<?php echo getUrlForGroup('dispute/my/order_number/'.$order['id']);?>" target="_blank">A dispute</a> is opened for this order. Disputes are available only after EP Manager's approval.</span>
				</div>
			<?php }?>

			<ul class="order-detail__params">
			<?php if($order['order_type'] == "po" && in_array($order['status_alias'], array('shipper_assigned', 'payment_processing'))){?>
				<li class="order-detail__params-item">
					<div class="order-detail__params-name">Paid:</div>
					<div class="order-detail__paid-percent">
						<div class="wr-bar-b">
							<div class="bar-b" style="width: <?php echo (empty($bills_percent) ? 0 : $bills_percent) . '%';?>"></div>
							<div class="bar-text"><?php echo empty($bills_percent) ? 0 : number_format($bills_percent, 2, '.', '');?>%</div>
						</div>
					</div>
				</li>
			<?php }?>

			<?php if($order['producing_status'] != ''){?>
				<li class="order-detail__params-item">
					<div class="order-detail__params-name">Producing status:</div>
					<div><?php echo $order['producing_status'];?></div>
				</li>
			<?php }?>
			</ul>

			<table class="order-detail-table order-detail__table">
				<thead>
					<tr>
						<th>Product</th>
						<?php if(in_array($order['status_alias'], array('shipping_completed', 'order_completed'))){?>
							<th class="w-40 vam"></th>
						<?php }?>
						<th class="w-75 tar">Quantity</th>
						<th class="w-135 tar">Amount</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($purchased_products as $_item_ordered){?>
						<tr>
							<td>
								<?php if($_item_ordered['type'] == 'item'){ ?>
									<div class="order-detail__product grid-card">
										<div
                                            class="order-detail__product-image image-card3 call-function call-action"
                                            data-callback="callMoveByLink"
                                            data-js-action="link:move-by-link"
                                            data-target="_blank"
                                            data-link="<?php echo getUrlForGroup('items/ordered/'.strForURL($_item_ordered['name']).'-'.$_item_ordered['id_ordered_item']);?>"
                                        >
											<span class="link">
                                                <img
                                                    class="image"
                                                    src="<?php echo getDisplayImageLink(array('{ID}' => $_item_ordered['id_snapshot'], '{FILE_NAME}' => $_item_ordered['image']), 'items.snapshot', array( 'thumb_size' => 1 ));?>"
                                                    alt="<?php echo cleanOutput($_item_ordered['name']);?>"
                                                />
											</span>
										</div>

										<div class="order-detail__product-detail grid-text">
											<div class="grid-text__item text-nowrap">
												<a class="order-detail__product-ttl" href="<?php echo getUrlForGroup('items/ordered/'.strForURL($_item_ordered['name']).'-'.$_item_ordered['id_ordered_item']);?>" target="_blank"><?php echo $_item_ordered['name']?></a>
												<div class="order-detail__product-rating">
													<?php if(!is_null($_item_ordered['snapshot_reviews_count']) && $_item_ordered['snapshot_reviews_count'] > 0){?>
														<span class="order-detail__product-rating-item">
															<i class="ep-icon ep-icon_star txt-orange"></i>
															<span class="lh-15"><?php echo $_item_ordered['snapshot_rating'];?></span>
														</span>
													<?php }?>

													<?php if(!is_null($_item_ordered['snapshot_reviews_count']) && $_item_ordered['snapshot_reviews_count'] > 0 && !empty($_item_ordered['detail_ordered'])){?>
														<span class="delimeter"></span>
													<?php }?>

													<?php if(!empty($_item_ordered['detail_ordered'])){?>
														<span class="order-detail__product-rating-item">
															<?php echo $_item_ordered['detail_ordered'];?>
														</span>
													<?php }?>
												</div>
											</div>
										</div>
									</div>
								<?php }else{?>
									<?php echo $_item_ordered['name']?>
								<?php }?>
							</td>

							<?php if(in_array($order['status_alias'], array('shipping_completed', 'order_completed'))){?>
								<td class="w-40 vam">

									<?php
										$_disputes_actions = array(
											'open_dispute' => empty($order_disputes[$_item_ordered['id_ordered_item']]) && empty($order_disputes[0]) && have_right('buy_item'),
											'dispute_opened' => !empty($order_disputes[$_item_ordered['id_ordered_item']]) && ((have_right('manage_disputes') && $order_disputes[$_item_ordered['id_ordered_item']]['status'] == 'processing') || have_right('buy_item'))
										);
									?>
									<?php if($_item_ordered['type'] == 'item' && $expire > 0 && $order['status_alias'] == 'shipping_completed' && ( $_disputes_actions['open_dispute'] || $_disputes_actions['dispute_opened'])){?>
										<div class="dropdown">
											<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
												<i class="ep-icon ep-icon_menu-circles circle-30 pulse-shadow-animation"></i>
											</a>

											<div class="dropdown-menu">
												<?php if($_disputes_actions['open_dispute']){?>
													<span
														class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax"
														data-title="Open dispute"
														title="Open dispute"
														data-fancybox-href="<?php echo getUrlForGroup('dispute/popup_forms/add/item/'.$order['id'].'/'.$_item_ordered['id_ordered_item']);?>"
														data-dashboard-class="inputs-40"
													>
														<i class="ep-icon ep-icon_low"></i><span class="txt">Open dispute</span>
													</span>
												<?php } elseif($_disputes_actions['dispute_opened']){?>
													<span
														class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax"
														data-title="Dispute timeline"
														title="Dispute timeline"
														data-fancybox-href="<?php echo getUrlForGroup('dispute/popup_forms/add_notice/'.$order_disputes[$_item_ordered['id_ordered_item']]['id']);?>"
													>
														<i class="ep-icon ep-icon_low"></i><span class="txt">Dispute timeline</span>
													</span>
												<?php }?>
											</div>
										</div>
									<?php }?>

                                    <?php if($_item_ordered['type'] == 'item' && $order['status_alias'] == 'order_completed'){?>
                                        <?php $_exist_review_on_item = empty($user_ordered_items_for_reviews[$_item_ordered["id_ordered_item"]]);?>
										<div class="dropdown">
											<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#" <?php echo addQaUniqueIdentifier("page__my-orders__order-information_dropdown-menu")?>>
                                                <i
                                                    class="ep-icon ep-icon_menu-circles circle-30 lh-30
                                                    <?php if((!$_exist_review_on_item && have_right('write_reviews')) || ($_exist_review_on_item && !have_right('write_reviews'))){?>pulse-shadow-animation<?php }?>"
                                                ></i>
											</a>

											<div class="dropdown-menu dropdown-menu-right">
												<?php if(!$_exist_review_on_item && have_right('write_reviews')){?>
													<span
														class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax"
														data-dashboard-class="inputs-40 add-review-popup"
														data-title="Add review"
														title="Add review"
														data-fancybox-href="<?php echo getUrlForGroup('reviews/popup_forms/add_review/order?order='.$order['id'].'&item='.$_item_ordered["id_item"]);?>"
                                                        <?php echo addQaUniqueIdentifier("page__my-orders__order-information_dropdown-menu_add-review")?>
													>
														<i class="ep-icon ep-icon_star-empty"></i><span class="txt">Add review</span>
													</span>
												<?php } else{?>
													<?php if($_exist_review_on_item){?>
														<span
															class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax"
															data-dashboard-class="inputs-40"
															data-title="View review"
															title="View review"
															data-fancybox-href="<?php echo getUrlForGroup('reviews/popup_forms/details/'.$_item_ordered["id_ordered_item"].'?type=item-ordered')?>"
                                                            <?php echo addQaUniqueIdentifier("page__my-orders__order-information_dropdown-menu_view-review")?>
														>
															<i class="ep-icon <?php if(isset($user_ordered_items_for_reviews[$_item_ordered["id_ordered_item"]])) echo 'ep-icon_star-empty'; else echo 'ep-icon_star';?>"></i><span class="txt">View review</span>
														</span>
													<?php } else if(!have_right('write_reviews')){?>
														<span class="dropdown-item" data-dashboard-class="inputs-40">
															<i class="ep-icon ep-icon_star-empty"></i><span class="txt">No reviews</span>
														</span>
													<?php }?>
												<?php }?>
												<!-- <span
														class="dropdown-item link-ajax fancybox fancybox.ajax"
														data-dashboard-class="inputs-40"
														data-title="Items reviews"
														title="What is Review?"
														data-fancybox-href="<?php //echo getUrlForGroup('user_guide/popup_forms/show_doc/items_reviews_doc?user_type=buyer')?>"
													>
													<i class="ep-icon ep-icon_info-stroke"></i><span class="txt">What is Review?</span>
												</span> -->
											</div>
										</div>
									<?php }?>
								</td>
							<?php }?>
							<td class="w-75 vam"><?php echo $_item_ordered['quantity']?></td>
							<td class="w-135 vam"><?php echo get_price($_item_ordered['total_price'])?></td>
						</tr>
					<?php }?>
				</tbody>
			</table>

			<?php $_colspan_totals = in_array($order['status_alias'], array('shipping_completed', 'order_completed'))?3:2;?>
			<table class="order-detail__table">
				<tbody>
					<tr>
						<td colspan="<?php echo $_colspan_totals;?>" class="tar"><span class="txt-gray">Subtotal</span></td>
						<td class="w-135"><span><?php echo get_price($order['price']);?></span></td>
					</tr>
					<tr>
						<td colspan="<?php echo $_colspan_totals;?>" class="tar"><span class="txt-gray">Discount</span></td>
						<td class="w-135"><span><?php echo $order['discount'];?>%</span></td>
					</tr>
					<tr>
						<td colspan="<?php echo $_colspan_totals;?>" class="tar"><span class="txt-gray">Shipping</span></td>
						<td class="w-135"><span><?php echo get_price($order['ship_price']);?></span></td>
					</tr>
				</tbody>
				<tfoot>
					<td>
						<?php $total = ($order['final_price']+$order['ship_price']);?>

						<div class="fs-10 lh-24">
							<?php if($cookie->cookieArray['currency_key'] !== 'USD'){?>
							*Real price for payment is $ <?php echo get_price($total, false);?>
							<?php }?>
						</div>
					</td>
					<td colspan="<?php echo $_colspan_totals - 1;?>"><span class="txt-medium">Total</span></td>
					<td class="w-135">
						<span class="txt-medium">
							<?php echo get_price($total);?>
							<?php if($cookie->cookieArray['currency_key'] !== 'USD'){?>
							*
							<?php }?>
						</span>
					</td>
				</tfoot>
			</table>

			<div class="order-detail__ship">

				<div class="order-detail__ship-item">
					<span class="order-detail__ship-name">Order manager:</span>

					<?php if($order['ep_manager']){?>
					<span class="pr-10">
						<?php echo $ep_manager_info['user_name']?>
					</span>

					<?php echo !empty($btnChatManager2) ? $btnChatManager2 : ''; ?>

					<?php }else{?>
						<span class="pr-10">
							-
						</span>
					<?php }?>
				</div>

				<?php if(!empty($company_info)){?>
					<div class="order-detail__ship-item">
						<span class="order-detail__ship-name">Seller:</span>
						<span>
							<a class="link-black" href="<?php echo getCompanyURL($company_info);?>" target="_blank">
								<img
									class="h-20 vam"
									src="<?php echo getDisplayImageLink(array('{ID}' => $company_info['id_company'], '{FILE_NAME}' => $company_info['logo_company']), 'companies.main', array( 'thumb_size' => 0 ));?>">
								<?php echo $company_info['name_company']?>
							</a>
						</span>


						<?php echo !empty($btnChatSeller2) ? $btnChatSeller2 : ''; ?>
					</div>
				<?php }?>

				<?php if(have_right('manage_seller_orders') || have_right('manage_shipper_orders')){?>
					<?php $user_name = $user_buyer_info['username']; ?>
					<div class="order-detail__ship-item">
						<span class="order-detail__ship-name">Buyer:</span>
						<span>
							<a class="link-black" href="<?php echo getUserLink($user_name, $order['id_buyer'], 'buyer'); ?>" target="_blank">
								<img class="h-20 vam" src="<?php echo getDisplayImageLink(array('{ID}' => $order['id_buyer'], '{FILE_NAME}' => $user_buyer_info['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $user_buyer_info['user_group'] ));?>">
								<?php echo $user_name;?>
							</a>

							<?php if(!empty($company_buyer_info)){?>
								<span>(<?php echo $company_buyer_info['company_name'];?>)</span>
							<?php }?>
						</span>

                        <?php echo !empty($btnChatBuyer2) ? $btnChatBuyer2 : ''; ?>
					</div>
				<?php }?>

				<?php if($order['seller_delivery_area'] > 0){?>
					<div class="order-detail__ship-item">
						<span class="order-detail__ship-name">Seller's available area for delivering, in km:</span>
						<span><?php echo $order['seller_delivery_area'];?></span>
					</div>
				<?php }?>

				<?php if(have_right('manage_seller_orders') || have_right('buy_item')){?>

					<div class="order-detail__ship-item">
						<span class="order-detail__ship-name">Freight Forwarder:</span>
						<span class="pr-10">
							<?php if(!empty($shipper_info)){?>
								<?php if($order['shipper_type'] != 'ep_shipper'){?>
									<a class="link-black" href="<?php echo $shipper_info['shipper_contacts'];?>" target="_blank">
										<img src="<?php echo $shipper_info['shipper_logo']?>" class="h-20 vam" alt="<?php echo $shipper_info['shipper_name']?>">
										<?php echo $shipper_info['shipper_name']?>
									</a>
								<?php }else{?>
									<a class="link-black" href="<?php echo $shipper_info['shipper_url'];?>" target="_blank">
										<img src="<?php echo $shipper_info['shipper_logo']?>" class="h-20 vam" alt="<?php echo $shipper_info['shipper_name']?>">
										<?php echo $shipper_info['shipper_name']?>
									</a>
								<?php }?>
							<?php } else{?>
								-
							<?php }?>
						</span>


						<?php echo !empty($btnChatShipper2) ? $btnChatShipper2 : ''; ?>
					</div>

				<?php }?>

				<div class="order-detail__ship-item">
					<span class="order-detail__ship-name">Ship from:</span>
					<span><?php if(!empty($order['ship_from'])) echo $order['ship_from']; else echo '-';?></span>
				</div>
				<div class="order-detail__ship-item">
					<span class="order-detail__ship-name">Ship to:</span>
					<span><?php if(!empty($order['ship_to'])) echo $order['ship_to']; else echo '-';?></span>
				</div>
				<div class="order-detail__ship-item">
					<span class="order-detail__ship-name">Tracking info:</span>
					<span>
						<?php if(!empty($order['tracking_info'])){?>
							<?php echo $order['tracking_info'];?>
						<?php } else{?>
							-
						<?php }?>
					</span>
				</div>
			</div>
		</div>
	</div>

	<div class="display-n" id="js-hidden-status-text">
		<div class="order-status__content">
		<?php if(!empty($description_title['mandatory'])){?>
			<?php echo $description_title['mandatory'];?>
		<?php }?>

		<?php if(!empty($description_title['optional'])){?>
			<h3 class="order-status__ttl">Optional</h3>
			<?php echo $description_title['optional'];?>
		<?php }?>
		</div>
	</div>
</div><!-- order-detail -->
<script type="text/template" id="js-hidden-how-to-order-documents-text">
	<div class="ep-tinymce-text pl-5">
		<p>Export Portal’s system supports the following document types: <strong>Invoice</strong>, <strong>Contract</strong> and <strong>Personalized Document</strong>.</p>
		<p>The <strong>Personalized document</strong> is <strong>added by users</strong>.</p>
		<p>The <strong>Invoice</strong> and <strong>Contract</strong> are <span class="txt-red">autogenerated</span> by the system.</p>
		<p>The <strong>Invoice</strong> is generated <strong>for the buyer and seller exclusively</strong>, and it can only be downloaded.</p>

		<p class="mb-0">The <strong>Contract</strong> is generated <strong>for the buyer</strong>:</p>
		<ol class="pb-0 pl-30">
			<li class="pb-15">Please <span class="txt-red">download it</span> by clicking the <strong>Download</strong> button;</li>
			<li class="pb-15">Sign;</li>
			<li class="pb-15">Upload the <span class="txt-red">signed version</span> and <span class="txt-red">assign to the next signatory</span>.</li>
		</ol>

		<p class="mb-0">A <strong>document’s</strong> list of <strong>actions</strong> can be viewed by clicking the menu button with the <strong>three dots</strong>:</p>
		<ul class="pb-0 pl-30">
			<li class="pb-15"><strong class="txt-red">Download</strong> - available to the owner of the document and at specific stage of the document’s history you were assigned to.</li>
			<li class="pb-15"><strong class="txt-red">Upload</strong> - available to the person currently assigned to the document.</li>
			<li class="pb-15"><strong class="txt-red">Document History</strong> - displays the list of stages for each document. To <strong>see all the actions</strong> on each stage, please click the menu button with the <strong>three dots</strong>.</li>
		</ul>

		<p class="mb-0"><strong>You have received a document that needs to be processed (signed, paid)</strong>? Please follow these steps:</p>
		<ol class="pb-0 pl-30">
			<li class="pb-15">Click the menu button with the <strong>three dots</strong>.</li>
			<li class="pb-15">Click the <strong>Download</strong> button to download the document.</li>
			<li class="pb-15"><strong>Process (sign, pay for)</strong> the downloaded document.</li>
			<li class="pb-15">After processing <strong>scan or take a photo</strong> of your document, to have a digital version of it.</li>
		</ol>

		<p class="mb-0"><strong>To upload the processed document</strong>, please follow the steps below:</p>
		<ol class="pb-0 pl-30">
			<li class="pb-15">Click the <strong>View Order Documents</strong> button to open the list of documents;</li>
			<li class="pb-15"><strong>Find</strong> the corresponding <strong>document</strong>;</li>
			<li class="pb-15">Click the menu button with the <strong>three dots</strong> and find the <strong>Upload</strong> button.</li>
			<li class="pb-15">Click the <strong>Upload</strong> button to upload the <strong>processed document</strong>.</li>
		</ol>

		<p class="mb-0"><strong>To add a new personalized document</strong>, please follow these steps:</p>
		<ol class="pb-0 pl-30">
			<li class="pb-15">Click the <strong>Action on order</strong> button;</li>
			<li class="pb-15">Click on <strong>Add Order Documents</strong>;</li>
			<li class="pb-15">Fill in the required form fields;</li>
			<li class="pb-15">Upload the document by clicking the <strong>Upload file</strong> button;</li>
			<li class="pb-15">After uploading the document, click the button <strong>Confirm</strong> to submit the Order Document.</li>
		</ol>
	</div>
</script>

<script>
(function() {
	"use strict";

	window.vatingOrderStatusModal = ({
		init: function (params) {
			vatingOrderStatusModal.self = this;
			vatingOrderStatusModal.dontShow = false;
			vatingOrderStatusModal.$modal = $(params[0]);
			vatingOrderStatusModal.$hiddenOrderStatusActionsModal = $('#js-hidden-status-actions');
			vatingOrderStatusModal.$mainOrderStatusModal = vatingOrderStatusModal.$modal.find('.js-order-status-modal');

            vatingOrderStatusModal.self.initListiners();
			// vatingOrderStatusModal.self.openVaitingActivation();
		},
        initListiners: function(){

            vatingOrderStatusModal.$mainOrderStatusModal.on('click', '.js-btn-close', function(e){
                e.preventDefault();
                var $this = $(this);
                vatingOrderStatusModal.dontShow = $('.modal .js-dont-show-more').prop('checked');
                vatingOrderStatusModal.self.closeAndSetOrderStatusView($this);
            })
        },
		closeAndSetOrderStatusView: function(){
			if(
				vatingOrderStatusModal.dontShow
				&& !existCookie('_ep_view_order_status')
			){
				vatingOrderStatusModal.$hiddenOrderStatusActionsModal.html("");
				setCookie('_ep_view_order_status', 1, 7);
			}

			BootstrapDialog.closeAll();
		}
	});

}());

var ifExitsOrderStatusView = function(){
	if(!existCookie('_ep_view_order_status')){
		if($('.order-detail__status .info-dialog-100').length){
			$('.order-detail__status .info-dialog-100').trigger('click');
		}
	}
}

<?php $user_have_to_confirm_extend = have_right('manage_seller_orders') && $extend_info['status_seller'] == 'awaiting' || have_right('buy_item') && $extend_info['status_buyer'] == 'awaiting' || have_right('manage_shipper_orders') && $extend_info['status_shipper'] == 'awaiting';?>
<?php if(!empty($extend_info) && $user_have_to_confirm_extend && (int)$order['cancel_request'] == 0){?>
	var confirmAutoExtend = function(){
		var order = intval('<?php echo $order['id']?>');
		$.ajax({
			type: 'POST',
			url: '<?php echo getUrlForGroup('order/ajax_order_operations/confirm_extend');?>',
			data: { order : order },
			dataType: 'JSON',
			success: function(resp){
				if(resp.mess_type == 'success'){
					current_page = 1;
					loadOrderList(true);
                    showOrder(order);
                    open_result_modal({
                        content: resp.message,
                        type: 'success',
                        closable: true,
                        buttons: [
                            {
                                label: translate_js({ plug: "BootstrapDialog", text: "close" }),
                                cssClass: "btn btn-light",
                                action: function (dialog) {
                                    dialog.close();
                                },
                            }
                        ]
                    });
				} else{
                    systemMessages( resp.message, resp.mess_type );
                }

			}
		});
	}

	var declineAutoExtend = function(){
		$('.order-detail__top-btns .js-btn-cancel-request').trigger('click');
	}

	var openModalAutoExtend = function(){
        open_result_modal({
            content: 'The Order Status expires soon. We will extend the time for another <?php echo $extend_days;?> days if you agree.',
            type: 'question',
            buttons: [
                {
                    label: 'I agree',
					cssClass: 'btn-success mnw-80',
					action: function(dialogRef){
						confirmAutoExtend();
						dialogRef.close();
					}
                },
                {
                    label: 'I disagree',
                    cssClass: 'btn-danger mnw-80',
                    action: function(dialogRef){
                        declineAutoExtend();
                        dialogRef.close();
                    }
                }
            ]
        });
	}
<?php }?>

var showStatusModal = function(dialog){
	window.vatingOrderStatusModal.init(dialog.getModalFooter());
}

$(function(){
	$('.order-detail').on('click', ".info-dialog-100", function(e){
		var $thisBtn = $(this);
		e.preventDefault();

		var storedMessage = $thisBtn.data('message') || null;
		var storedContent = $thisBtn.data('content') || null;
		var storedActions = $thisBtn.data('actions') || null;
		var message = '';
		var actions = '';

		if(null !== storedMessage){
			message = storedMessage;
		} else if(null !== storedContent){
			message = $(storedContent).html();
		}

		if(null !== storedActions){
			actions = ($(storedActions).html() || '').trim();
		}

		open_info_dialog_100($thisBtn.data('title'), message, actions);
	});

	if(($('.main-data-table').length > 0) && ($(window).width() < 768)){
		$('.main-data-table').addClass('main-data-table--mobile');
	}

	mobileDataTable($('.main-data-table'));

	if(($('.order-detail-table').length > 0) && ($(window).width() < 768)){
		$('.order-detail-table').addClass('order-detail__table--mobile');
	}

	mobileDataTable($('.order-detail-table'));

	<?php if(!empty($extend_info) && $user_have_to_confirm_extend && (int)$order['cancel_request'] == 0){?>
		openModalAutoExtend();
	<?php }else{?>
		ifExitsOrderStatusView();
	<?php }?>
})

jQuery(window).on('resizestop', function () {

	if($('.order-detail-table').length > 0){
		if($(window).width() < 768){
			$('.order-detail-table').addClass('order-detail__table--mobile');
		}else{
			$('.order-detail-table').removeClass('order-detail__table--mobile');
		}
	}
});
</script>
