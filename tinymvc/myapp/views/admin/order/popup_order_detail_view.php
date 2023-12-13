<script src="<?php echo __SITE_URL;?>public/plug_admin/jquery-countdown-2-2-0/jquery.countdown.js"></script>

<div class="wr-modal-b">
   <div class="modal-b__content w-700 p-0">
		<div class="order-detail-b h-auto">
			<div class="order-detail-b-top">
				<div class="text-b">
					<p class="name">Order <?php echo orderNumber($order['id']);?></p>
					<p class="status">
						<i class="ep-icon <?php echo $order['status_icon'];?> fs-16 lh-16"></i> <?php echo $order['status'];?>

						<?php if(!empty($description_title)){?>
						<span class="info-dialog i-info ep-icon ep-icon_info" data-title="Status description" data-content="#js-hidden-status"></span>
						<?php }?>
					</p>

					<?php if(!empty($description_title)){?>
					<div id="js-hidden-status" class="display-n">
						<div class="order-status">
							<?php if(!empty($description_title['mandatory'])){?>
								<h3 class="order-status__ttl">Mandatory</h3>
								<?php echo $description_title['mandatory'];?>
							<?php }?>

							<?php if(!empty($description_title['optional'])){?>
								<h3 class="order-status__ttl">Optional</h3>
								<?php echo $description_title['optional'];?>
							<?php }?>

							<?php if(isset($description_video_title)){?>
								<div class="order-status__btns">
									<a class="btn btn-primary call-function" data-title="Video" data-callback="orderFancyboxStatusVideo" href="<?php echo __IMG_URL.'public/img/orders_status/'.strtolower(user_group_type()).'/'.$description_video_title;?>">Video Guide</a>
								</div>
							<?php }?>
						</div>
					</div>
					<?php }?>
				</div>
				<div class="clearfix"></div>
				<div class="order-detail__status-timer"></div>
				<?php if($order['order_type'] == "po"){?>
				<div class="paid-percent-b clearfix">
					<p>Paid</p>
					<div class="wr-bar-b">
						<div class="bar-b" style="<?php echo 'width: ' . (empty($bills_percent) ? 0 : $bills_percent) . '%';?>"></div>
						<div class="bar-text"><?php echo (!empty($bills_percent))?$bills_percent:0; ?>%</div>
					</div>
				</div>
				<?php }?>
			</div>

			<div class="order-detail-b-middle">
				<?php if($order['producing_status'] != ''){?>
				<p class="mb-5"><strong>Producing status: </strong><?php echo $order['producing_status'];?></p>
				<?php }?>
				<table>
					<thead>
						<tr>
							<th>Product name</th>
							<?php if(in_array($order['status_alias'], array('shipping_completed', 'order_completed'))){?>
								<th class="w-40 vam"></th>
							<?php }?>
							<th class="w-85 tar">Qty</th>
							<th class="w-130 tar">Amount</th>
						</tr>
					</thead>
					<tbody>
						<?php if($order['id_invoice']){?>
							<?php $ordered = json_decode($order['products'], true);?>
							<?php foreach($ordered as $item){?>
								<tr>
									<?php if(!empty($item['id_item'])){ ?>
										<td class="pb-5">
											<a class="btn-veiw__item lh-21" href="<?php echo __SITE_URL;?>items/ordered/<?php echo strForURL($item['name'])?>-<?php echo $item['id_ordered_item']; ?>" target="_blank"><?php echo $item['name']?></a>
										</td>
									<?php }else{?>
										<td class="pb-5"><?php echo $item['name']?></td>
									<?php }?>

									<?php if(in_array($order['status_alias'], array('shipping_completed', 'order_completed'))){?>
										<td class="w-40 vam">
										<?php if($order['status_alias'] == 'shipping_completed'){?>
											<?php if(empty($order_disputes[$item['id_item']]) && empty($order_disputes[0]) && have_right('buy_item')){?>
												<a class="fancyboxValidateModal fancybox.ajax" data-title="Open dispute" title="Open dispute" href="<?php echo __SITE_URL;?>dispute/popup_forms/add/item/<?php echo $order['id'].'/'.$item['id_item'];?>"><i class="ep-icon ep-icon_low txt-red lh-21 fs-18"></i></a>
											<?php } elseif(!empty($order_disputes[$item['id_item']])){?>
												<?php if(have_right('manage_disputes') && $order_disputes[$item['id_item']]['status'] == 'processing'){?>
													<a class="fancyboxValidateModal fancybox.ajax" data-title="Dispute timeline" title="Dispute timeline" href="<?php echo __SITE_URL;?>dispute/popup_forms/add_notice/<?php echo $order_disputes[$item['id_item']]['id'];?>"><i class="ep-icon ep-icon_low lh-21 fs-18"></i></a>
												<?php } elseif(have_right('buy_item')){?>
													<a class="fancyboxValidateModal fancybox.ajax" data-title="Dispute timeline" title="Dispute timeline" href="<?php echo __SITE_URL;?>dispute/popup_forms/add_notice/<?php echo $order_disputes[$item['id_item']]['id'];?>"><i class="ep-icon ep-icon_low lh-21 fs-18"></i></a>
												<?php }?>
											<?php }?>
										<?php }?>
										<?php if($order['status_alias'] == 'order_completed' && isset($item["id_item"])){
													if(have_right('manage_seller_orders')){
														$title_full = 'Review received';
														$title_empty = 'No reviews';
													}elseif(have_right('buy_item')){
														$title_empty = 'Add review';
														$title_full = 'View review';
													}
											if(isset($user_ordered_items_for_reviews[$item["id_item"]]) && have_right('buy_item')){?>
												<a class="ep-icon ep-icon_star-empty fs-16 lh-18 fancyboxValidateModal fancybox.ajax" href="<?php echo __SITE_URL?>reviews/popup_forms/add_review/?item=<?php echo $order['ordered'][$item["id_item"]]['id_item']; ?>" data-title="<?php echo $title_empty;?>" title="<?php echo $title_empty;?>"></a>
											<?php } else{?>
												<a class="ep-icon <?php if(isset($user_ordered_items_for_reviews[$item["id_item"]])) echo 'ep-icon_star-empty'; else echo 'ep-icon_star';?> fs-16 lh-18 fancyboxValidateModal fancybox.ajax" href="<?php echo __SITE_URL?>reviews/popup_forms/view_review_by_ordered/<?php echo $order['ordered'][$item["id_item"]]['id_item'];?>/?ordered=<?php echo $item['id_item']; ?>" data-title="<?php echo $title_full;?>" title="<?php echo $title_full;?>"></a>
											<?php }?>
										<?php }?>
										</td>
									<?php }?>
									<td class="pb-5 w-85 vam"><?php echo $item['quantity']?></td>
									<td class="pb-5 w-130 vam"><?php echo get_price($item['total_price'])?></td>
								</tr>
							<?php }?>
						<?php } else{?>
							<?php foreach($order['ordered'] as $item){?>
								<tr>
									<td class="pb-5"><a class="btn-veiw__item lh-21" href="items/ordered/<?php echo strForURL($item['title']) . '-' .$item['id_ordered_item']; ?>" target="_blank"><?php echo $item['title']?></a></td>
									<?php if(in_array($order['status_alias'], array('shipping_completed', 'order_completed'))){?>
										<td></td>
									<?php }?>
									<td class="pb-5 w-85"><?php echo $item['quantity_ordered']?></td>
									<td class="pb-5 w-130"><?php echo get_price($item['price_ordered']);?></td>
								</tr>
							<?php }?>
						<?php }?>
						<tr>
							<td></td>
							<?php if(in_array($order['status_alias'], array('shipping_completed', 'order_completed'))){?>
								<td></td>
							<?php }?>
							<td><span>Subtotal</span></td>
							<td><span><?php echo get_price($order['price']);?></span></td>
						</tr>
						<tr>
							<td></td>
							<?php if(in_array($order['status_alias'], array('shipping_completed', 'order_completed'))){?>
								<td></td>
							<?php }?>
							<td><span>Discount</span></td>
							<td><span><?php echo $order['discount'];?>%</span></td>
						</tr>
						<tr>
							<td></td>
							<?php if(in_array($order['status_alias'], array('shipping_completed', 'order_completed'))){?>
								<td></td>
							<?php }?>
							<td><span>Shipping</span></td>
							<td><span><?php echo get_price($order['ship_price']);?></span></td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<?php $total = ($order['final_price']+$order['ship_price']);?>
							<td>*<span class="fs-10">Real price for payment is $ <?php echo get_price($total, false);?></span></td>
							<?php if(in_array($order['status_alias'], array('shipping_completed', 'order_completed'))){?>
								<td></td>
							<?php }?>
							<td class="w-85">Total</td>
							<td class="w-130">
							<?php echo get_price($total);?>*
							</td>
						</tr>
					</tfoot>
				</table>

				<div >
					<p class="lh-20"><strong>Ship from:</strong> <?php if(!empty($order['ship_from'])) echo $order['ship_from']; else echo '-';?></p>
					<p class="lh-20"><strong>Ship to:</strong> <?php if(!empty($order['ship_to'])) echo $order['ship_to']; else echo '-';?></p>
					<p class="lh-25"><strong>Freight Forwarder:</strong>
						<?php if(!empty($shipper_info)){?>
							<img src="<?php echo $shipper_info['shipper_logo']?>" class="h-20 vam" alt="<?php echo $shipper_info['shipper_name']?>">
							<?php echo $shipper_info['shipper_name']?>
							<?php if($order['shipper_type'] == 'ep_shipper'){?>
								( <?php echo !empty($shipper_info['btnChat'])? $shipper_info['btnChat']:'';?> )
							<?php } else{?>
								( <a class="btn-contact" href="<?php echo $shipper_info['shipper_contacts'];?>" target="_blank" title="<?php echo $shipper_info['shipper_name']?> contact page"><i class="ep-icon ep-icon_link"></i> View website</a> )
							<?php }?>
						<?php } else{?>
						-
						<?php }?>
					</p>
					<p class="lh-20">
						<strong>Tracking info:</strong>
						<?php if(!empty($order['tracking_info'])){?>
							<?php if($order['shipper_type'] == 'ep_shipper'){?>
								<?php echo $order['tracking_info'];?>
							<?php } else{?>
								<?php $ishippment_info = json_decode($order['tracking_info']);?>
								Code: <?php echo $ishippment_info->tracking_code;?>
							<?php }?>
						<?php } else{?>
						-
						<?php }?>
					</p>
				</div>
                <?php
                if(!empty($order['order_summary'])){
                    $order_summary = explode('||',$order['order_summary']);
                    $order_timeline = array_reverse(json_decode('[' . $order['order_summary'] . ']', true));
                }
                ?>
                <div>
                    <p class="tac"><i class="ep-icon ep-icon_clock"></i> Order timeline</p>
                    <table class="table data table-bordered table-striped mt-5 mb-0">
                        <thead>
                            <tr role="row">
                                <th class="w-130">Date</th>
                                <th class="w-100">Member</th>
                                <th class="mnw-100">Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div class="overflow-y-a mh-300">
                    <table class="table data table-bordered table-striped m-0">
                        <tbody>
                            <?php foreach($order_timeline as $order_log){?>
                                <tr>
                                    <td class="w-130"><?php echo formatDate($order_log['date'], 'm/d/Y H:i:s A');?></td>
                                    <td class="w-100"><?php echo $order_log['user'];?></td>
                                    <td class="mnw-100"><?php echo $order_log['message'];?></td>
                                </tr>
                            <?php }?>
                        </tbody>
                    </table>
                </div>
			</div>
		</div><!-- order-detail-b -->
	</div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    <?php if($show_timeline){?>
		var expire = <?php echo $expire; ?>;
		var selectedDate = new Date().valueOf() + expire;
		$('.order-detail__status-timer').countdown(selectedDate.toString(), {defer: true})
		.on('update.countdown', function(event) {
			var format_clock = '<div class="txt-green">%D days %H hours %M min</div>';
			if(expire < 7200000){
				format_clock = '<div class="txt-red">%D days %H hours %M min</div>';
			}
			$(this).html(event.strftime(format_clock));
		}).on('finish.countdown', function(event) {
			$(this).html('<div class="txt-red">The time for this status has expired!</div>');
		}).countdown('start');
    <?php }?>

	$('#order_status_description').qtip({
		content: {
			text: function(event, api) {
				return $(this).attr('qtip-content');
			}
		},
		show: {
			event: 'mouseenter'
		},
		hide: {
			event: 'mouseleave'
		},
		position: {
			my: 'bottom center',
			at: 'top center',
			adjust: {
				mouse: false,
				scroll: false
			}
		},
		style: {
			widget: true,
			classes: 'qtip-shadow'
		}
	});
});
</script>
