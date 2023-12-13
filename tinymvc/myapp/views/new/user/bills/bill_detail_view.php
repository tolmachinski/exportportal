<div id="order-detail-<?php echo $offer['id_offer']?>" class="order-detail">
	<div class="order-detail__scroll">
		<div class="order-detail__top">
			<ul class="order-detail__params">
				<li class="order-detail__params-item order-detail__params-item--double">
					<div class="order-detail__param-col">
						<div class="order-detail__params-name">Number:</div>
						<div class="order-detail__number"><?php echo orderNumber($bill['id_bill']); ?></div>
					</div>
					<div class="order-detail__param-col">
						<div class="order-detail__params-name">Status:</div>
						<div class="order-detail__status">
							<span class="info-dialog cur-pointer" data-title="Detail in <span class='txt-gray'><?php echo $status[$bill['status']]['description']; ?></span>" data-content="#js-hidden-status"><?php echo $status[$bill['status']]['description'];?></span>
						</div>

						<?php if (!empty($bill['bill_description'])) { ?>
                            <div id="js-hidden-status" class="display-n">
                                <div class="order-status">
                                    <div class="order-status__content">
                                        <?php echo $bill['bill_description']; ?>
                                    </div>
                                </div>
                            </div>
						<?php } ?>
					</div>
				</li>

				<?php if (!in_array($bill['status'], array('paid', 'confirmed','unvalidated'))) { ?>
                    <li class="order-detail__params-item">
                        <div class="order-detail__params-name">Remaining:</div>
                        <div class="order-detail__time">
                            <div class="order-detail__status-timer"></div>
                        </div>
                    </li>
                <?php } ?>

				<li class="order-detail__params-item">
					<div class="order-detail__params-name"><?php echo $status[$bill['status']]['text_date']; ?></div>
					<div class="">
						<?php echo getDateFormat($bill[$status[$bill['status']]['date']]); ?>
					</div>
				</li>
            </ul>

			<div class="order-detail__top-btns">
				<div class="order-detail__top-btns-item">
					<a class="btn btn-light btn-block info-dialog" data-title="Detail in <span class='txt-gray'><?php echo $status[$bill['status']]['description'];?></span>" data-content="#js-hidden-status" href="#">
						<i class="ep-icon ep-icon_info txt-gray fs-16"></i>
						<span class="pl-5">Detail</span>
					</a>
				</div>
				<div class="order-detail__top-btns-item">
					<div class="dropdown">
						<a class="btn btn-primary btn-block dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i class="ep-icon ep-icon_menu-circles"></i>
							<span class="pl-5 pr-5">Action on Bill</span>
                        </a>

						<div class="dropdown-menu dropdown-menu-right">
							<?php if ('init' === $bill['status'] && $expire > 0) { ?>
                                <a
                                    class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                    data-fancybox-href="<?php echo getUrlForGroup("/payments/popups_payment/pay_bill/{$bill['id_bill']}"); ?>"
                                    data-title="Payment"
                                    data-body-class="fancybox-position-ios-fix"
                                    title="Pay now"
                                    href="#">
                                    <i class="ep-icon ep-icon_dollar-circle"></i>
                                    <span class="txt">Pay now</span>
                                </a>
							<?php } ?>

                            <?php if ($extend_btn) { ?>
                                <a
                                    class="dropdown-item fancyboxValidateModal fancybox.ajax"
                                    data-fancybox-href="<?php echo getUrlForGroup("extend/popup_form/bill/{$bill['id_bill']}"); ?>"
                                    data-title="Request extend payment time"
                                    title="Request extend payment time"
                                    href="#">
                                    <i class="ep-icon ep-icon_clock-stroke2"></i>
                                    <span class="txt">Request extend payment time</span>
                                </a>
							<?php } ?>

							<?php if ($show_extend_btn) { ?>
								<a class="dropdown-item fancybox fancybox.ajax" href="<?php echo __SITE_URL . 'extend/popup_form/detail/' . $bill['extend_request'];?>" data-title="Extend request details">
									<i class="ep-icon ep-icon_clock-stroke2"></i>
									<span class="txt">Extend request detail</span>
								</a>
							<?php } ?>

							<a class="dropdown-item" href="<?php echo __SITE_URL . 'billing/invoice/' . $bill['id_bill'];?>" title="View / Download invoice" target="_blank">
								<i class="ep-icon ep-icon_file-in"></i>
								<span class="txt">View / Download invoice</span>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="order-detail__scroll-padding">
			<table class="table table-bordered mt-25">
				<caption class="tac mb-10"><i class="ep-icon ep-icon_clock fs-16 vat lh-22"></i> Bill timeline</caption>
				<thead>
					<tr>
						<th>Note</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach($bill['note'] as $note){ ?>
					<tr>
						<td>
							<div class="txt-gray"><?php echo getDateFormat($note['date_note']);?></div>
                            <div class="grid-text">
                                <div class="grid-text__item">
                                    <?php echo urldecode($note['note']); ?>
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
