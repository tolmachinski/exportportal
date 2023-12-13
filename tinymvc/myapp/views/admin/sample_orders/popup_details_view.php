<div class="wr-modal-b">
   <div class="modal-b__content w-700 p-0">
		<div class="order-detail-b h-auto">
			<div class="order-detail-b-top">
				<div class="text-b">
					<p class="name">Order <?php echo orderNumber($sample['id']);?></p>
					<p class="status">
						<i class="ep-icon <?php echo $sample['status']['icon'];?> fs-16 lh-16"></i> <?php echo $sample['status']['name'];?>

						<?php if (!empty($sample['status']['description'])) {?>
							<span class="info-dialog i-info ep-icon ep-icon_info" data-title="Status description" data-content="#js-hidden-status"></span>
						<?php }?>
					</p>

					<?php if (!empty($sample['status']['description'])) {?>
						<div id="js-hidden-status" class="display-n">
							<div class="order-status">
								<?php if (!empty($sample['status']['description']['mandatory'])) {?>
									<h3 class="order-status__ttl">Mandatory</h3>
									<?php echo $sample['status']['description']['mandatory'];?>
								<?php }?>

								<?php if (!empty($sample['status']['description']['optional'])) {?>
									<h3 class="order-status__ttl">Optional</h3>
									<?php echo $sample['status']['description']['optional'];?>
								<?php }?>
							</div>
						</div>
					<?php }?>
				</div>
			</div>

			<div class="order-detail-b-middle">
				<table>
					<thead>
						<tr>
							<th>Product name</th>
							<th class="w-85 tar">Qty</th>
							<th class="w-130 tar">Amount</th>
						</tr>
					</thead>
					<tbody>
						<?php if ($sample['id_invoice']) {?>
							<?php foreach ($products as $item) {?>
								<tr>
									<td class="pb-5">
										<a class="btn-veiw__item lh-21" href="<?php echo cleanOutput(makeItemUrl((int) $item['item_id'], $item['name'])); ?>" target="_blank"><?php echo cleanOutput($item['name']); ?></a>
									</td>
									<td class="pb-5 w-85 vam"><?php echo cleanOutput($item['quantity']); ?></td>
									<td class="pb-5 w-130 vam"><?php echo cleanOutput(get_price($item['total_price'])); ?></td>
								</tr>
							<?php } ?>
						<?php }?>
						<tr>
							<td></td>
							<td><span>Subtotal</span></td>
							<td><span><?php echo get_price($sample['price']);?></span></td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<td>*<span class="fs-10">Real price for payment is $ <?php echo get_price($sample['price'], false);?></span></td>
							<td class="w-85">Total</td>
							<td class="w-130">
								<?php echo get_price($sample['final_price']);?>*
							</td>
						</tr>
					</tfoot>
				</table>

				<div>
					<p class="lh-20">
						<strong>Ship from: </strong>
						<?php echo empty($sample['ship_from']) ? '-' : $sample['ship_from'];?>
					</p>
					<p class="lh-20">
						<strong>Ship to: </strong>
						<?php echo empty($sample['ship_to']) ? '-' : $sample['ship_to'];?>
					</p>
					<p class="lh-25">
						<strong>Freight Forwarder: </strong>
						<?php if (!empty($shipper)) {?>
							<img src="<?php echo $shipper['logo'];?>" class="h-20 vam" alt="<?php echo $shipper['name'];?>">
							<?php echo $shipper['name'];?>
							( <a class="btn-contact" href="<?php echo $shipper['contact_url'];?>" target="_blank" title="<?php echo $shipper['name']?> contact page"><i class="ep-icon ep-icon_link"></i> View website</a> )
						<?php } else {?>
						-
						<?php }?>
					</p>
					<p class="lh-20">
						<strong>Tracking info: </strong>
						<?php echo empty($sample['tracking_info']) ? '-' : $sample['tracking_info'];?>
					</p>
				</div>
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
							<?php if (!empty($sample['purchase_order_timeline'])) {?>
								<?php foreach ($sample['purchase_order_timeline'] as $order_log) {?>
									<tr>
										<td class="w-130"><?php echo getDateFormat($order_log['date'], DATE_ATOM);?></td>
										<td class="w-100"><?php echo $order_log['user'];?></td>
										<td class="mnw-100"><?php echo $order_log['message'];?></td>
									</tr>
								<?php }?>
							<?php }?>
                        </tbody>
                    </table>
                </div>
			</div>
		</div>
	</div>
</div>
