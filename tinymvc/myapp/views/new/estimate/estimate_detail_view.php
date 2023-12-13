<?php $cookie = tmvc::instance()->controller->cookies; ?>

<div id="order-detail-<?php echo $estimate['id_request_estimate']?>" class="order-detail">
	<div class="order-detail__scroll">
		<div class="order-detail__top">
			<ul class="order-detail__params">
				<li class="order-detail__params-item order-detail__params-item--double">
					<div class="order-detail__param-col">
						<div class="order-detail__params-name">Number:</div>
						<div class="order-detail__number"><?php echo orderNumber($estimate['id_request_estimate']);?></div>
					</div>
					<div class="order-detail__param-col">
						<div class="order-detail__params-name">Status:</div>
						<div class="order-detail__status <?php echo $estimate_status['title_color'];?>">
							<span class="info-dialog-100 cur-pointer" data-title="What's next in <span class='txt-gray'><?php echo $estimate_status['title'];?></span>" data-content="#js-hidden-status-text" data-actions="#js-hidden-status-actions"><?php echo $estimate_status['title'];?></span>
						</div>

						<div class="display-n">
							<div id="js-hidden-status-text">
								<div class="order-status__content">
									<?php if(!empty($estimate_status['whats_next'])){?>
										<?php if(!empty($estimate_status['whats_next'][$estimate_status_user]['text']['mandatory'])){?>
											<?php echo $estimate_status['whats_next'][$estimate_status_user]['text']['mandatory'];?>
										<?php }?>

										<?php if(!empty($estimate_status['whats_next'][$estimate_status_user]['text']['optional'])){?>
											<h3 class="order-status__ttl">Optional</h3>
											<?php echo $estimate_status['whats_next'][$estimate_status_user]['text']['optional'];?>
										<?php }?>
									<?php } else if(!empty($estimate_status['description'])){?>
										<?php echo $estimate_status['description'];?>
									<?php }?>
								</div>
							</div>

							<div id="js-hidden-status-actions">
								<?php if(!cookies()->exist_cookie('_ep_view_estimate_status')){?>
									<div class="js-order-status-modal inputs-40 order-status__btns flex-ai--c">
										<label class="custom-checkbox">
											<input class="js-dont-show-more" type="checkbox" name="dont_show_more">
											<span class="custom-checkbox__text">Don't show more</span>
										</label>

										<a class="btn btn-dark js-btn-close w-130" href="#">Ok</a>
									</div>
								<?php }?>
							</div>
						</div>
					</div>
				</li>

				<li class="order-detail__params-item">
					<div class="order-detail__params-name">Remaining:</div>
					<div class="order-detail__time">
						<?php if($estimate['state_buyer'] == 1 && $estimate['state_seller'] == 1){?>
							<div class="lh-20 txt-red">This Estimate will be deleted on <?php echo getDateFormat(date_plus(180, 'days', $estimate['update_date']), "Y-m-d H:i:s"); ?></div>
						<?php } else if($estimate['status'] == 'initiated'){?>
							<?php if(have_right('buy_item') && $estimate['state_buyer'] == 1 || have_right('manage_seller_estimate') && $estimate['state_seller'] == 1){?>
								<div class="order-detail__status-timer"></div>
							<?php } else{?>
								<div class="lh-20 txt-red">This Estimate will be moved to archive on <?php echo getDateFormat(date_plus(7, 'days', $estimate['update_date']), "Y-m-d H:i:s"); ?></div>
							<?php }?>
						<?php } else{?>
							<div class="order-detail__status-timer"></div>
						<?php }?>
					</div>
				</li>
			</ul>

			<div class="order-detail__top-btns">
				<div class="order-detail__top-btns-item">
					<span class="btn btn-light btn-block info-dialog-100" data-title="What's next in <span class='txt-gray'><?php echo cleanOutput($estimate_status['title']);?></span>"  data-content="#js-hidden-status-text" data-actions="#js-hidden-status-actions" href="#">
						<i class="ep-icon ep-icon_info txt-gray fs-16"></i>
						<span class="pl-5">What's next</span>
					</span>
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
						</div>
					</div>
				</div>
				<?php }?>

				<div class="order-detail__top-btns-item">
					<div class="dropdown">
						<a class="btn btn-primary btn-block dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i class="ep-icon ep-icon_menu-circles"></i>
							<span class="pl-5 pr-5">Action on Estimate</span>
						</a>
						<?php
							$remove_in_status = array('initiated','declined');
							if (have_right('manage_seller_estimate')) {
								$decline_in_status = array('new', 'wait_seller');
								$state_user = 'state_seller';
							} elseif (have_right('buy_item')) {
								$decline_in_status = array('new', 'wait_buyer');
								$state_user = 'state_buyer';
							}

							$nodetail_actions = true;
						?>

						<div class="dropdown-menu dropdown-menu-right">
							<?php if(($estimate['status'] == 'accepted')){?>
								<?php if(have_right('manage_seller_estimate')){?>
									<div class="dropdown-item">
										<div class="info-alert-b">
											<i class="ep-icon ep-icon_info-stroke"></i>
											Waiting for buyer to start the order.
										</div>
									</div>
									<?php $nodetail_actions = false;?>
								<?php }?>
								<?php if(have_right('buy_item')){?>
									<a class="dropdown-item fancyboxValidateModal fancybox.ajax" href="<?php echo __SITE_URL; ?>estimate/popup_forms/ship_to/<?php echo $estimate['id_request_estimate'];?>" data-title="Start order">
										<i class="ep-icon ep-icon_marker-stroke2"></i>
										<span class="txt">Start Order</span>
									</a>
									<?php $nodetail_actions = false;?>
								<?php }?>
							<?php }?>

							<?php if(have_right('buy_item') && ($estimate['status'] == 'wait_buyer')){?>
								<a class="dropdown-item confirm-dialog" data-message="Are you sure you want accept this Estimate?" data-callback="change_status" data-action="confirm_estimate" href="estimate-<?php echo $estimate['id_request_estimate'];?>">
									<i class="ep-icon ep-icon_ok-circle"></i>
									<span class="txt">Accept</span>
								</a>
								<?php $nodetail_actions = false;?>
							<?php }?>

							<?php if((have_right('manage_seller_estimate') || have_right('buy_item')) && ($estimate[$state_user] == 1 || in_array($estimate['status'], $remove_in_status))){?>
								<a class="dropdown-item confirm-dialog" data-message="Are you sure you want delete this estimate?" data-callback="change_status" data-action="remove_estimate" href="estimate-<?php echo $estimate['id_request_estimate'];?>">
									<i class="ep-icon ep-icon_trash-stroke"></i>
									<span class="txt">Delete</span>
								</a>
								<?php $nodetail_actions = false;?>
							<?php }?>

							<?php if((have_right('manage_seller_estimate') || have_right('buy_item')) && ($estimate['status'] == 'initiated') && !$estimate[$state_user]){?>
								<a class="dropdown-item confirm-dialog" data-message="Are you sure you want add to archive this estimate?" data-callback="change_status" data-action="archived_estimate" href="estimate-<?php echo $estimate['id_request_estimate'];?>">
									<i class="ep-icon ep-icon_folder"></i>
									<span class="txt">Add to archive</span>
								</a>
								<?php $nodetail_actions = false;?>
							<?php }?>

							<?php if((have_right('manage_seller_estimate') || have_right('buy_item')) && in_array($estimate['status'], array('wait_buyer', 'wait_seller', 'new')) && !$estimate['archived']){?>
								<a class="dropdown-item fancyboxValidateModal fancybox.ajax" href="<?php echo __SITE_URL;?>estimate/popup_forms/resend_estimate/<?php echo $estimate['id_request_estimate'];?>" data-title="Discuss the Estimate">
									<i class="ep-icon ep-icon_comments-stroke"></i>
									<span class="txt">Discuss</span>
								</a>
								<?php $nodetail_actions = false;?>
							<?php }?>

							<?php if(in_array($estimate['status'], $decline_in_status)){?>
								<a class="dropdown-item confirm-dialog" data-message="Are you sure you want declined this estimate?" data-callback="change_status" data-action="declined_estimate" href="estimate-<?php echo $estimate['id_request_estimate'];?>">
									<i class="ep-icon ep-icon_remove-circle"></i>
									<span class="txt">Decline</span>
								</a>
								<?php $nodetail_actions = false;?>
							<?php }?>

							<?php if($nodetail_actions){?>
								<span class="dropdown-item">No available actions</span>
							<?php }?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="order-detail__scroll-padding">
			<ul>
				<?php if(have_right('buy_item')){?>
					<li class="order-detail__params-item">
						<div class="order-detail__params-name">Seller:</div>
						<div class="order-detail__time">
							<a class="link-black" href="<?php echo getCompanyURL($seller_info);?>" target="_blank" title="<?php echo cleanOutput($seller_info['name_company']);?> contact page">
								<img
									class="h-20 vam"
									src="<?php echo getDisplayImageLink(array('{ID}' => $seller_info['id_company'], '{FILE_NAME}' => $seller_info['logo_company']), 'companies.main', array( 'thumb_size' => 0 ));?>"
									alt="<?php echo cleanOutput($seller_info['name_company']);?>">
								<?php echo $seller_info['name_company'];?>
							</a>

							<?php echo !empty($btnChatSeller2) ? $btnChatSeller2 : ''; ?>
						</div>
					</li>
				<?php } elseif(have_right('manage_seller_estimate')){?>
					<li class="order-detail__params-item">
						<div class="order-detail__params-name">Buyer:</div>
						<div class="order-detail__time">
							<a class="link-black" href="<?php echo getUserLink($buyer_info['user_name'], $buyer_info['idu'], 'buyer');?>">
								<img class="h-20 vam" src="<?php echo getDisplayImageLink(array('{ID}' => $buyer_info['idu'], '{FILE_NAME}' => $buyer_info['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $buyer_info['user_group'] ));?>" alt="<?php echo cleanOutput($buyer_info['user_name']);?>" />
								<?php echo $buyer_info['user_name'];?>
							</a>

							<?php echo !empty($btnChatBuyer2) ? $btnChatBuyer2 : ''; ?>
						</div>
					</li>
				<?php }?>
			</ul>
			<table class="order-detail-table order-detail__table">
				<thead>
					<tr>
						<th>Product name</th>
						<th class="w-75 tar">Quantity</th>
						<th class="w-135 tar">Amount</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="order-detail__product-detail grid-text">
								<div class="grid-text__item">
									<a class="order-detail__prod-link" href="<?php echo __SITE_URL; ?>item/<?php echo strForURL($estimate['title']).'-'.$estimate['id_item']; ?>" target="_blank"><?php echo $estimate['title']?></a>
									<div class="order-detail__product-rating">
										<?php if(!empty($estimate['detail_item_params'])){?>
											<span class="order-detail__product-rating-item">
												<?php echo $estimate['detail_item_params'];?>
											</span>
										<?php }?>
									</div>
								</div>
							</div>
						</td>
						<td><?php echo $estimate['quantity']?></td>
						<td>
							<?php echo get_price($estimate['price'])?>
							<?php if($cookie->cookieArray['currency_key'] !== 'USD'){?>
							*
							<?php }?>
						</td>
					</tr>
				</tbody>
			</table>

			<table class="order-detail-table order-detail__table">
				<thead>
					<tr>
						<th class="tar">Total</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="tar"><?php echo get_price($estimate['quantity']*$estimate['price'])?></td>
					</tr>
				</tbody>
			</table>

			<?php if(!empty($estimate['log'])){?>
				<table class="table table-bordered mt-25">
					<caption class="tac mb-10"><i class="ep-icon ep-icon_clock fs-16 vat lh-22"></i> Estimate timeline</caption>
					<thead>
						<tr>
							<th class="w-250 tac">Date</th>
							<th class="w-100 tac">User</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($estimate['log'] as $key => $estimate_timeline){ ?>
						<tr>
							<td class="tac"><?php echo formatDate($estimate_timeline['date']); ?></td>
							<td class="tac"><?php if(isset($estimate_timeline['poster'])) echo $estimate_timeline['poster']; else echo 'System'; ?></td>
						</tr>
						<tr>
							<td class="fs-14 bdb-1-black" colspan="2">
								<?php if(isset($estimate_timeline['price'])){?>
									<strong>Estimated price: </strong> $ <?php echo get_price($estimate_timeline['price'], false); ?> <br>
								<?php }?>
								<?php if(isset($estimate_timeline['quantity'])){?>
									<strong>Estimated quantity: </strong> <?php echo $estimate_timeline['quantity']; ?> <br>
								<?php }?>

								<?php if($estimate_timeline['poster'] == 'Buyer'){?>
									<strong>Message from buyer: </strong> <?php echo $estimate_timeline['message']; ?>
								<?php }else{?>
									<strong>Message from seller: </strong> <?php echo $estimate_timeline['message']; ?>
								<?php }?>
							</td>
						</tr>
						<?php }?>
					</tbody>
				</table>
			<?php }?>
		</div>
	</div>
</div>

<script>
	(function() {
		"use strict";

		window.vatingEstimateStatusModal = ({
			init: function (params) {
				vatingEstimateStatusModal.self = this;
				vatingEstimateStatusModal.dontShow = false;
				vatingEstimateStatusModal.$modal = $(params[0]);
				vatingEstimateStatusModal.$hiddenEstimateStatusActionsModal = $('#js-hidden-status-actions');
				vatingEstimateStatusModal.$mainEstimateStatusModal = vatingEstimateStatusModal.$modal.find('.js-order-status-modal');

				vatingEstimateStatusModal.self.initListiners();
				// vatingEstimateStatusModal.self.openVaitingActivation();
			},
			initListiners: function(){
				vatingEstimateStatusModal.$mainEstimateStatusModal.on('click', '.js-btn-close', function(e){
					e.preventDefault();
					var $this = $(this);
                    vatingEstimateStatusModal.dontShow = $('.modal .js-dont-show-more').prop('checked');
					vatingEstimateStatusModal.self.closeAndSetOrderStatusView($this);
				});
			},
			closeAndSetOrderStatusView: function(){
				if(
					vatingEstimateStatusModal.dontShow
					&& !existCookie('_ep_view_estimate_status')
				){
					vatingEstimateStatusModal.$hiddenEstimateStatusActionsModal.html("");
					setCookie('_ep_view_estimate_status', 1, 7);
				}

				BootstrapDialog.closeAll();
			}
		});

	}());

	var ifExitsEstimateStatusView = function(){
		if(!existCookie('_ep_view_estimate_status')){
			if($('.order-detail__status .info-dialog-100').length){
				$('.order-detail__status .info-dialog-100').trigger('click');
			}
		}
	}

	var showStatusModal = function(dialog){
		window.vatingEstimateStatusModal.init(dialog.getModalFooter());
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

		$('.order-popover').popover({
			container: 'body',
			trigger: 'hover'
		});

		if(($('.order-detail-table').length > 0) && ($(window).width() < 768)){
			$('.order-detail-table').addClass('order-detail__table--mobile');
		}

		mobileDataTable($('.order-detail-table'));

		ifExitsEstimateStatusView();
	});

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
