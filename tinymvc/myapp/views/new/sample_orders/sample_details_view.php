<div id="order-samples--details-<?php echo cleanOutput($sample['id']); ?>" class="order-detail">
	<div class="order-detail__scroll wr-orders-detail overflow-y-a">
		<div class="order-detail__top">
			<ul class="order-detail__params">
				<li class="order-detail__params-item order-detail__params-item--double">
					<div class="order-detail__param-col">
						<div class="order-detail__params-name">Number:</div>
						<div class="order-detail__number"><?php echo cleanOutput(orderNumber($sample['id'])); ?></div>
					</div>
					<div class="order-detail__param-col">
						<div class="order-detail__params-name">Status:</div>
							<div class="order-detail__status <?php if ('canceled' === $status['alias']) { ?> txt-red<?php } ?> js-sample-status">
							<span class="info-dialog-100 cur-pointer js-info-dialog"
								data-title="What's next in <span class='txt-gray'><?php echo cleanOutput($status['name'] ?? ''); ?></span>"
								data-content="#js-hidden-status-text"
								data-actions="#js-hidden-status-actions">
								<?php echo cleanOutput($status['name']); ?>
							</span>
						</div>
					</div>
				</li>
				<li class="order-detail__params-item">
					<div class="order-detail__time">
						<?php if ($sample['is_cancelling']) { ?>
							<div class="lh-20 txt-red">There are cancel request for this order. Please check the
								<span class="link-ajax info-dialog-ajax"
									data-href="<?php echo cleanOutput(getUrlForGroup("/sample_orders/popup_forms/timeline/{$sample['id']}")); ?>"
									data-title="Order <?php echo cleanOutput(orderNumber($sample['id'])); ?> timeline">
									order timeline
								</span> for more info.
							</div>
						<?php } ?>
					</div>
				</li>
            </ul>

			<div class="order-detail__top-btns">
				<div class="order-detail__top-btns-item">
					<span class="btn btn-light btn-block info-dialog-100 js-info-dialog"
						data-title="What's next in <span class='txt-gray'><?php echo cleanOutput($status['name'] ?? ''); ?></span>"
						data-content="#js-hidden-status-text"
						data-actions="#js-hidden-status-actions">
						<i class="ep-icon ep-icon_info txt-gray fs-16"></i>
						<span class="pl-5">What's next</span>
					</span>
				</div>

				<?php if (have_right('manage_messages')) { ?>
					<div class="order-detail__top-btns-item">
						<div class="dropdown">
							<a class="btn btn-light btn-block dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="ep-icon txt-gray ep-icon_chat2 fs-16"></i>
								<span class="pl-5 pr-5">Write to</span>
								<i class="ep-icon ep-icon_arrow-down fs-9"></i>
							</a>

							<div class="dropdown-menu dropdown-menu-right">
                                <?php
                                    if (!$is_seller && !empty($seller)) {
                                        echo !empty($seller['btnChat'])?$seller['btnChat']:'';
                                    }

                                    if ($is_seller && !empty($buyer)) {
                                        echo !empty($buyer['btnChat'])?$buyer['btnChat']:'';
                                    }
                                ?>
                                <?php //if (!$is_seller && !empty($seller)) { ?>
                                    <?php //echo \contactUserButton((int) $seller['idu'] ?: null, $resource_options, 'Chat with seller', '<i class="ep-icon ep-icon_envelope-open"></i>', ['title' => "Chat with seller"]); ?>
                                <?php //} ?>
                                <?php //if ($is_seller && !empty($buyer)) { ?>
                                    <?php //echo \contactUserButton((int) $buyer['idu'] ?: null, $resource_options, 'Chat with buyer', '<i class="ep-icon ep-icon_envelope-open"></i>', ['title' => "Chat with buyer"]); ?>
                                <?php //} ?>
							</div>
						</div>
					</div>
				<?php } ?>

				<div class="order-detail__top-btns-item">
					<div class="dropdown">
						<a class="btn btn-primary btn-block dropdown-toggle <?php if ('new-order' === $status['alias']) { ?>pulse-shadow-animation<?php } ?>"
							data-toggle="dropdown"
							data-flip="false"
							aria-haspopup="true"
							aria-expanded="false">
							<i class="ep-icon ep-icon_menu-circles fs-20"></i>
							<span class="pl-5">Action on order</span>
						</a>

						<div class="dropdown-menu dropdown-menu-right">
							<?php $is_edited = $purchase_order['is_edited'] ?? false; ?>
							<?php $is_confirmed = $purchase_order['is_confirmed'] ?? false; ?>
							<?php $is_deliverable = $purchase_order['is_deliverable'] ?? false; ?>
							<?php $is_confirmable = $purchase_order['is_confirmable'] ?? false; ?>

							<?php if ('new-order' === $status['alias']) { ?>
								<?php if (!$is_confirmed && have_right('edit_sample_order') && have_right('create_sample_order')) { ?>
									<span class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax <?php if (!$is_edited || !$is_confirmable) { ?>pulse-shadow-animation<?php } ?>"
										data-fancybox-href="<?php echo getUrlForGroup("/sample_orders/popup_forms/edit_purchase_order/{$sample['id']}"); ?>"
										<?php if (!$is_edited) { ?>
											data-title="Create Purchase Order (PO)"
										<?php } else { ?>
											data-title="Edit Purchase Order (PO)"
										<?php } ?>>
										<i class="ep-icon ep-icon_file-text"></i>
										<span class="txt">
											<?php if (!$is_edited) { ?>
												Create Purchase Order (PO)
											<?php } else { ?>
												Edit Purchase Order (PO)
											<?php } ?>
										</span>
									</span>
								<?php } ?>

								<?php if (!$is_confirmed && have_right('edit_sample_order') && have_right('set_delivery_address_to_sample_order')) { ?>
									<span class="dropdown-item cur-pointer fancybox.ajax fancyboxValidateModal <?php if (!$is_deliverable) { ?>pulse-shadow-animation<?php } ?>"
										data-fancybox-href="<?php echo cleanOutput(getUrlForGroup("/sample_orders/popup_forms/delivery_address/{$sample['id']}")); ?>"
										data-title="<?php echo cleanOutput(sprintf('Delivery address for order %', orderNumber($sample['id']))); ?>">
										<i class="ep-icon ep-icon_truck-globe"></i>
										<span class="txt">Edit delivery address</span>
									</span>
								<?php } ?>
							<?php } ?>

							<?php if ('shipping-in-progress' === $status['alias']) { ?>
								<?php if (have_right('edit_sample_order') && have_right('create_sample_order')) { ?>
									<span class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax <?php if (null === $sample['delivery_date']) { ?>pulse-shadow-animation<?php } ?>"
										data-fancybox-href="<?php echo getUrlForGroup("/sample_orders/popup_forms/tracking_info/{$sample['id']}"); ?>"
										data-title="<?php echo cleanOutput(sprintf('Tracking info for order %', orderNumber($sample['id']))); ?>">
										<i class="ep-icon ep-icon_pencil"></i>
										<span class="txt">Edit tracking info</span>
									</span>
								<?php } ?>

								<?php if (null !== $sample['delivery_date'] && have_right('edit_sample_order') && have_right('request_sample_order')) { ?>
									<span class="dropdown-item cur-pointer confirm-dialog pulse-shadow-animation"
										data-order="<?php echo cleanOutput($sample['id']); ?>"
										data-message="Are you sure you want to confirm the delivery?"
										data-callback="confirmDelivery">
										<i class="ep-icon ep-icon_truck"></i><span class="txt">Confirm delivery</span>
									</span>
								<?php } ?>
							<?php } ?>

							<?php if (have_right('view_sample_orders')) { ?>
								<?php if ($is_edited) { ?>
									<?php $need_confirmation = 'new-order' === $status['alias']
										&& !$is_confirmed
										&& $is_confirmable
										&& $is_deliverable
										&& have_right('edit_sample_order')
										&& have_right('request_sample_order');
									?>

									<span class="dropdown-item link-ajax fancyboxValidateModal fancybox.ajax <?php if ($need_confirmation) { ?>pulse-shadow-animation<?php } ?>"
										data-fancybox-href="<?php echo getUrlForGroup("/sample_orders/popup_forms/view_purchase_order/{$sample['id']}"); ?>"
										<?php if ($need_confirmation) { ?>
											data-title="Confirm Purchase Order (PO)"
										<?php } else { ?>
											data-title="Purchase Order (PO) details"
										<?php } ?>>
										<i class="ep-icon ep-icon_file-text"></i>
										<?php if ($need_confirmation) { ?>
											<span class="txt">Confirm Purchase Order (PO)</span>
										<?php } else { ?>
											<span class="txt">Purchase Order (PO) details</span>
										<?php } ?>
									</span>
								<?php } else { ?>
									<span class="dropdown-item cur-pointer call-systmess"
										data-message="<?php echo cleanOutput(translate('sample_orders_po_is_not_created'));?>"
										data-type="warning">
										<i class="ep-icon ep-icon_file-text"></i>
										<span class="txt">Purchase Order (PO) details</span>
									</span>
								<?php } ?>
							<?php } ?>

							<?php if ('payment-processing' === $status['alias']) { ?>
								<?php if (have_right('request_sample_order') && have_right('view_sample_orders')) { ?>
									<span class="dropdown-item link-ajax fancybox fancybox.ajax pulse-shadow-animation"
										data-dashboard-class="inputs-40"
										data-fancybox-href="<?php echo getUrlForGroup("/sample_orders/popup_forms/bill_list/{$sample['id']}"); ?>"
										data-title="<?php echo cleanOutput(sprintf('The Sample Order %s bills list', orderNumber($sample['id']))); ?>">
										<i class="ep-icon ep-icon_dollar-circle"></i><span class="txt">Bills list</span>
									</span>
								<?php } ?>
							<?php } ?>

							<div class="dropdown-divider"></div>

							<span class="dropdown-item cur-pointer fancybox.ajax fancyboxValidateModal"
								data-fancybox-href="<?php echo cleanOutput(getUrlForGroup("/sample_orders/popup_forms/timeline/{$sample['id']}")); ?>"
								data-title="<?php echo cleanOutput(sprintf('Order %s timeline', orderNumber($sample['id']))); ?>">
								<i class="ep-icon ep-icon_hourglass"></i>
								<span class="txt">View sample timeline</span>
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="order-detail__scroll-padding">
			<?php if (in_array($status['alias'], array('shipping-in-progress', 'order-completed')) && $status['is_disputed']) { ?>
				<div class="error-alert-b mb-15">
					<i class="ep-icon ep-icon_low"></i>
					<span>
						<a href="<?php echo cleanOutput(getUrlForGroup("/dispute/my/order_number/{$order['id']}")); ?>" target="_blank">
							A dispute
						</a>
						is opened for this order. Disputes are available only after EP Manager's approval.
					</span>
				</div>
			<?php } ?>

			<table class="order-detail-table order-detail__table js-products-table">
				<thead>
					<tr>
						<th>Product</th>
						<th class="w-75 tar">Quantity</th>
						<th class="w-135 tar">Amount</th>
					</tr>
				</thead>

				<tbody>
					<?php foreach ($products as $product) { ?>
						<tr>
							<td>
								<?php if ('sample' === $product['type']) { ?>
									<div class="order-detail__product grid-card">
										<div
                                            class="order-detail__product-image image-card3 call-function call-action"
                                            data-callback="callMoveByLink"
                                            data-js-action="link:move-by-link"
											data-link="<?php echo cleanOutput($product['url']); ?>"
											data-target="_blank"
                                        >
											<span class="link">
												<img class="image"
													src="<?php echo cleanOutput($product['photo']); ?>"
													alt="<?php echo cleanOutput($product['name']); ?>"/>
											</span>
										</div>

										<div class="order-detail__product-detail grid-text">
											<div class="grid-text__item text-nowrap">
												<a class="order-detail__product-ttl" href="<?php echo cleanOutput($product['url']); ?>" target="_blank">
													<?php echo cleanOutput($product['name']); ?>
												</a>
												<div class="order-detail__product-rating">
													<?php if ((int) ($product['reviews'] ?? 0) > 0) { ?>
														<span class="order-detail__product-rating-item">
															<i class="ep-icon ep-icon_star txt-orange"></i>
															<span class="lh-15"><?php echo cleanOutput($product['rating']); ?></span>
														</span>

														<?php if (!empty($product['details'])) { ?>
															<span class="delimeter"></span>
														<?php } ?>
													<?php } ?>


													<?php if (!empty($product['details'])) { ?>
														<span class="order-detail__product-rating-item">
															<?php echo cleanOutput($product['details']); ?>
														</span>
													<?php } ?>
												</div>
											</div>
										</div>
									</div>
								<?php } else { ?>
									<?php echo cleanOutput($product['name']); ?>
								<?php } ?>
							</td>

							<td class="w-75 vam"><?php echo cleanOutput($product['quantity']); ?></td>
							<td class="w-135 vam"><?php echo cleanOutput(get_price($product['total_price'])); ?></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>

			<?php $spans = $sample['is_order_completed'] ? 3 : 2; ?>
			<table class="order-detail__table">
				<tfoot>
					<td>
						<div class="fs-10 lh-24">
							<?php if ('USD' !== cookies()->getCookieParam('currency_key')) { ?>
								*Real price for payment is $ <?php echo cleanOutput(get_price($sample['total'], false)); ?>
							<?php } ?>
						</div>
					</td>
					<td colspan="<?php echo cleanOutput($spans - 1); ?>">
						<span class="txt-medium">Total</span>
					</td>
					<td class="w-135">
						<span class="txt-medium">
							<?php echo cleanOutput(get_price($sample['total'])); ?>
							<?php if ('USD' !== cookies()->getCookieParam('currency_key')) { ?>
								*
							<?php } ?>
						</span>
					</td>
				</tfoot>
			</table>

			<div class="order-detail__ship">
				<?php if (!$is_seller) { ?>
					<div class="order-detail__ship-item">
						<span class="order-detail__ship-name">Seller:</span>
						<?php if (!empty($seller)) { ?>
							<span>
								<a class="link-black" href="<?php echo cleanOutput($seller['url']); ?>" target="_blank">
									<?php echo cleanOutput($seller['company_name'] ?? '-'); ?>
								</a>
							</span>
						<?php } else { ?>
							&mdash;
						<?php } ?>
					</div>
				<?php } ?>

				<?php if ($is_seller) { ?>
					<div class="order-detail__ship-item">
						<span class="order-detail__ship-name">Buyer:</span>
						<?php if (!empty($buyer)) { ?>
							<span>
								<a class="link-black" href="<?php echo cleanOutput($buyer['url']); ?>" target="_blank">
									<?php echo cleanOutput($buyer['fullname']); ?>
									<?php if (!empty($buyer['company_name'])) { ?>
										<span>(<?php echo cleanOutput($buyer['company_name']); ?>)</span>
									<?php } ?>
								</a>
							</span>
						<?php } else { ?>
							&mdash;
						<?php } ?>
					</div>
				<?php } ?>

				<div class="order-detail__ship-item">
					<span class="order-detail__ship-name">Freight Forwarder:</span>
					<?php if (!empty($shipper)) { ?>
						<span class="pr-10">
							<a class="link-black" href="<?php echo cleanOutput($shipper['url']); ?>" target="_blank">
								<img src="<?php echo cleanOutput($shipper['logo']); ?>" class="h-20 vam" alt="<?php echo cleanOutput($shipper['name']); ?>">
								<?php echo cleanOutput($shipper['name']); ?>
							</a>
						</span>

						<a href="<?php echo cleanOutput($shipper['contact_url']); ?>"
							class="link-ajax display-ib"
							title="Contact <?php echo cleanOutput($shipper['name']); ?>"
							target="_blank">
							Contact <?php echo cleanOutput($shipper['name']); ?>
						</a>
					<?php } else { ?>
						&mdash;
					<?php } ?>
				</div>

				<div class="order-detail__ship-item">
					<span class="order-detail__ship-name">Ship from:</span>
					<span><?php echo cleanOutput($sample['ship_from'] ?? '—'); ?></span>
				</div>
				<div class="order-detail__ship-item">
					<span class="order-detail__ship-name">Ship to:</span>
					<span><?php echo cleanOutput($sample['ship_to'] ?? '—'); ?></span>
				</div>
				<div class="order-detail__ship-item">
					<span class="order-detail__ship-name">Tracking info:</span>
					<span><?php echo cleanOutput($sample['tracking_info'] ?? '—'); ?></span>
				</div>
				<div class="order-detail__ship-item">
					<span class="order-detail__ship-name">Delivery date:</span>
					<span><?php echo getDateFormatIfNotEmpty($sample['delivery_date'], 'Y-m-d', 'm/d/Y', '—'); ?></span>
				</div>
				<div class="order-detail__ship-item">
					<span class="order-detail__ship-name">Buyer's request:</span>
					<?php if (!empty($sample['description'])) { ?>
						<p class="text-ws-pre-wrap"><?php echo cleanOutput($sample['description']); ?></p>
					<?php } else { ?>
						&mdash;
					<?php } ?>
				</div>
			</div>
		</div>
	</div>

	<div class="display-n">
		<div id="js-hidden-status-text">
			<div class="order-status__content">
				<?php echo arrayGet($status, 'description.mandatory') ?? ''; ?>
				<?php if ($status['description']['optional']) { ?>
					<h3 class="order-status__ttl">Optional</h3>
					<?php echo $status['description']['optional']; ?>
				<?php } ?>
			</div>
		</div>

		<?php if (!cookies()->exist_cookie('_ep_view_order_sample_status')) { ?>
			<div id="js-hidden-status-actions">
				<div class="js-order-status-modal inputs-40 order-status__btns flex-ai--c">
					<label class="custom-checkbox">
						<input class="js-dont-show-more" type="checkbox" name="dont_show_more">
						<span class="custom-checkbox__text">Don't show more</span>
					</label>

					<span class="btn btn-dark js-btn-close w-130">Ok</span>
				</div>
			</div>
		<?php } ?>
	</div>
</div>

<?php
    echo dispatchDynamicFragmentInCompatMode(
        "sample-order:detail",
        asset('public/plug/js/sample_orders/details.js', 'legacy'),
        sprintf(
            "function () {
                var sampleDetails = new SampleOrderDetailsModule(%s);
                mix(window, { confirmDelivery: function (button) { sampleDetails.confirmDelivery(button.data('order') || null) } }, false);
            }",
            json_encode(
                $params = [
                    'confirmUrl' => getUrlForGroup('/sample_orders/ajax_operations/confirm_delivery'),
                    'selectors'  => [
                        'statusText'     => '#js-hidden-status-text',
                        'statusActions'  => '#js-hidden-status-actions',
                        'detailsWrapper' => "#order-samples--details-{$sample['id']}",
                        'productsTable'  => "#order-samples--details-{$sample['id']} .js-products-table",
                        'statusModal'    => '.js-order-status-modal',
                        'dialogClose'    => '.js-btn-close',
                        'infoDialog'     => '.js-info-dialog',
                        'checkboxes'     => 'input[type=checkbox]',
                    ],
                ]
            ),
        ),
        [$params],
        true
    );
?>
