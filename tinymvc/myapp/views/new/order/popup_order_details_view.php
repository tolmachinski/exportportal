<?php $order_cancel_status = array('Late payment', 'Canceled by buyer', 'Canceled by seller', 'Canceled by EP');?>
<?php $cookie = tmvc::instance()->controller->cookies; ?>

<div id="order-detail-<?php echo $order['id']?>" class="order-detail p-0">
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
						<span><?php echo $order['status'];?></span>
					</div>
				</div>
			</li>
		</ul>
	</div>

	<div class="order-detail__scroll-padding">
		<table class="order-detail-table order-detail__table">
			<thead>
				<tr>
					<th>Product</th>
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
						<td class="w-75 vam"><?php echo $_item_ordered['quantity']?></td>
						<td class="w-135 vam"><?php echo get_price($_item_ordered['total_price'])?></td>
					</tr>
				<?php }?>
			</tbody>
		</table>

		<table class="order-detail__table">
			<tbody>
				<tr>
					<td></td>
					<td class="tar"><span class="txt-gray">Subtotal</span></td>
					<td class="w-135"><span><?php echo get_price($order['price']);?></span></td>
				</tr>
				<tr>
					<td></td>
					<td class="tar"><span class="txt-gray">Discount</span></td>
					<td class="w-135"><span><?php echo $order['discount'];?>%</span></td>
				</tr>
				<tr>
					<td></td>
					<td class="tar"><span class="txt-gray">Shipping</span></td>
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
				<td><span class="txt-medium">Total</span></td>
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
				</div>
			<?php }?>

			<?php if(have_right('manage_seller_orders') || have_right('manage_shipper_orders')){?>
				<?php $user_name = $user_buyer_info['username']; ?>
				<div class="order-detail__ship-item">
					<span class="order-detail__ship-name">Buyer:</span>
					<span>
						<a class="link-black" href="<?php echo getUrlForGroup('usr/'.strForURL($user_name).'-'.$order['id_buyer']);?>" target="_blank">
							<img class="h-20 vam" src="<?php echo getDisplayImageLink(array('{ID}' => $order['id_buyer'], '{FILE_NAME}' => $user_buyer_info['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $user_buyer_info['user_group'] ));?>">
							<?php echo $user_name;?>
						</a>

						<?php if(!empty($company_buyer_info)){?>
							<span>(<?php echo $company_buyer_info['company_name'];?>)</span>
						<?php }?>
					</span>
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

		<div class="pt-30">
			<p class="tac"><i class="ep-icon ep-icon_clock vat fs-16 lh-22"></i> Order timeline</p>
			<div class="js-modal-flex wr-modal-flex inputs-40">
				<div class="modal-flex__content">
					<?php
					if(!empty($order['order_summary'])){
						$order_summary = explode('||',$order['order_summary']);
						$order_timeline = array_reverse(json_decode('[' . $order['order_summary'] . ']', true));
					}
					?>
					<table class="main-data-table dataTable mt-15">
						<thead>
							<tr>
								<th class="w-130">Date</th>
								<th class="w-100">Member</th>
								<th class="mnw-100">Activity</th>
							</tr>
						</thead>
						<tbody class="tabMessage">
							<?php foreach($order_timeline as $order_log){?>
								<tr>
									<td><?php echo formatDate($order_log['date'], 'm/d/Y H:i:s A');?></td>
									<td><?php echo $order_log['user'];?></td>
									<td>
										<div class="grid-text">
											<div class="grid-text__item">
												<?php echo $order_log['message'];?>
											</div>
										</div>
									</td>
								</tr>
							<?php }?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

</div><!-- order-detail -->

<script>
	$(function(){
		if(($('.main-data-table').length > 0) && ($(window).width() < 768)){
			$('.main-data-table').addClass('main-data-table--mobile');
		}

		mobileDataTable($('.main-data-table'));

		if(($('.order-detail-table').length > 0) && ($(window).width() < 768)){
			$('.order-detail-table').addClass('order-detail__table--mobile');
		}

		mobileDataTable($('.order-detail-table'));

		$('.order-popover').popover({
			container: 'body',
			trigger: 'hover'
		});
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
